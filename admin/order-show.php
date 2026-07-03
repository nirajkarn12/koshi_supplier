<?php require_once('header.php'); ?>

<?php
// Check if 'id' is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: order.php');
    exit;
}

$id = (int)$_GET['id'];

// Fetch payment details
$statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE id = ?");
$statement->execute([$id]);
$payment = $statement->fetch(PDO::FETCH_ASSOC);

if(!$payment) {
    header('Location: order.php');
    exit;
}

// Fetch order items for this payment
$statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id = ?");
$statement->execute([$payment['payment_id']]);
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Helper function to check if a column exists (reuse logic)
function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0;
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Order Details</h1>
    </div>
    <div class="content-header-right">
        <a href="order.php" class="btn btn-primary btn-sm">Back to Orders</a>
        <a href="order-edit.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm">Edit Order</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body">
                    <!-- Order ID and Invoice ID -->
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr><th width="35%">Order ID</th><td><?php echo htmlspecialchars($payment['payment_id']); ?></td></tr>
                                <tr><th>Invoice ID</th><td><?php echo htmlspecialchars($payment['id']); ?></td></tr>
                                <tr><th>Customer Name</th><td><?php echo htmlspecialchars($payment['customer_name']); ?></td></tr>
                                <tr><th>Customer Email</th><td><?php echo htmlspecialchars($payment['customer_email']); ?></td></tr>
                                <tr><th>Customer Phone</th><td><?php echo htmlspecialchars($payment['customer_phone'] ?? ''); ?></td></tr>
                                <tr><th>Payment Method</th><td><?php echo htmlspecialchars($payment['payment_method']); ?></td></tr>
                                <tr><th>Payment Status</th><td><?php echo htmlspecialchars($payment['payment_status']); ?></td></tr>
                                <tr><th>Shipping Status</th><td><?php echo htmlspecialchars($payment['shipping_status']); ?></td></tr>
                                <tr><th>Payment Date</th><td><?php echo htmlspecialchars($payment['payment_date']); ?></td></tr>
                                <?php if(!empty($payment['notes'])): ?>
                                <tr><th>Notes</th><td><?php echo nl2br(htmlspecialchars($payment['notes'])); ?></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <tr><th>Subtotal</th><td><?php echo number_format((float)($payment['subtotal'] ?? 0), 2); ?></td></tr>
                                <tr>
                                    <th>Discount</th>
                                    <td>
                                        <?php
                                        $discountType = $payment['discount_type'] ?? '';
                                        $discountValue = (float)($payment['discount_value'] ?? 0);
                                        $discountAmount = (float)($payment['discount_amount'] ?? 0);
                                        if($discountType == 'percent') {
                                            echo $discountValue . '%';
                                        } elseif($discountType == 'amount') {
                                            echo 'Rs.' . number_format($discountValue, 2);
                                        } else {
                                            echo 'None';
                                        }
                                        echo ' (Amount: $' . number_format($discountAmount, 2) . ')';
                                        ?>
                                    </td>
                                </tr>
                                <tr><th>VAT (%)</th><td><?php echo number_format((float)($payment['vat_percent'] ?? 0), 2); ?>%</td></tr>
                                <tr><th>VAT Amount</th><td><?php echo number_format((float)($payment['vat_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Grand Total</th><td><strong><?php echo number_format((float)($payment['grand_total'] ?? 0), 2); ?></strong></td></tr>
                                <tr><th>Paid Amount</th><td><?php echo number_format((float)($payment['paid_amount'] ?? 0), 2); ?></td></tr>
                                <tr><th>Due Amount</th><td><?php echo number_format((float)($payment['due_amount'] ?? 0), 2); ?></td></tr>
                                <?php if(!empty($payment['txnid'])): ?>
                                <tr><th>Transaction ID</th><td><?php echo htmlspecialchars($payment['txnid']); ?></td></tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['card_number']) || !empty($payment['card_cvv']) || !empty($payment['card_month']) || !empty($payment['card_year'])): ?>
                                <tr><th>Card Details</th>
                                    <td>
                                        <?php
                                        $cardInfo = [];
                                        if(!empty($payment['card_number'])) $cardInfo[] = 'Number: ' . htmlspecialchars($payment['card_number']);
                                        if(!empty($payment['card_cvv'])) $cardInfo[] = 'CVV: ' . htmlspecialchars($payment['card_cvv']);
                                        if(!empty($payment['card_month'])) $cardInfo[] = 'Month: ' . htmlspecialchars($payment['card_month']);
                                        if(!empty($payment['card_year'])) $cardInfo[] = 'Year: ' . htmlspecialchars($payment['card_year']);
                                        echo implode('<br>', $cardInfo);
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['bank_transaction_info'])): ?>
                                <tr><th>Bank Transaction Info</th><td><?php echo nl2br(htmlspecialchars($payment['bank_transaction_info'])); ?></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Ordered Products -->
                    <h3>Ordered Products</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 0;
                                $subtotalCheck = 0;
                                foreach($orders as $item) {
                                    $counter++;
                                    $lineTotal = (float)($item['quantity'] * $item['unit_price']);
                                    $subtotalCheck += $lineTotal;
                                    echo '<tr>';
                                    echo '<td>' . $counter . '</td>';
                                    echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
                                    echo '<td>' . htmlspecialchars($item['size'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($item['color'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
                                    echo '<td>' . number_format((float)$item['unit_price'], 2) . '</td>';
                                    echo '<td>' . number_format($lineTotal, 2) . '</td>';
                                    echo '</tr>';
                                }
                                if($counter == 0) {
                                    echo '<tr><td colspan="7" class="text-center">No products found for this order.</td></tr>';
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Subtotal (from items)</th>
                                    <th><?php echo number_format($subtotalCheck, 2); ?></th>
                                </tr>
                                <?php if(!empty($payment['discount_amount']) && (float)$payment['discount_amount'] > 0): ?>
                                <tr>
                                    <th colspan="6" class="text-right">Discount</th>
                                    <th>- <?php echo number_format((float)$payment['discount_amount'], 2); ?></th>
                                </tr>
                                <?php endif; ?>
                                <?php if(!empty($payment['vat_amount']) && (float)$payment['vat_amount'] > 0): ?>
                                <tr>
                                    <th colspan="6" class="text-right">VAT</th>
                                    <th>+ <?php echo number_format((float)$payment['vat_amount'], 2); ?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th colspan="6" class="text-right">Grand Total</th>
                                    <th><strong><?php echo number_format((float)$payment['grand_total'], 2); ?></strong></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Paid</th>
                                    <th><?php echo number_format((float)$payment['paid_amount'], 2); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-right">Due</th>
                                    <th><?php echo number_format((float)$payment['due_amount'], 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <a href="order.php" class="btn btn-default">Back to Orders</a>
                    <a href="invoice.php?id=<?php echo $id; ?>" class="btn btn-info">View Invoice</a>
                    <a href="order-edit.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit Order</a>
                </div>
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>