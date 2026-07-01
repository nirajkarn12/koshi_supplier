<?php require_once('header.php'); ?>

<?php
require_once('../PHPMailer/src/PHPMailer.php');
require_once('../PHPMailer/src/SMTP.php');
require_once('../PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';
if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'Subject can not be empty\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'Subject can not be empty\n';
    }
    if($valid == 1) {

        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);

        // Getting Customer Email Address
        $cust_email = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=?");
        $statement->execute(array($_POST['cust_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $cust_email = $row['cust_email'];
        }

        // Getting Admin Email Address
        $admin_email = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $admin_email = $row['contact_email'];
        }

        $payment_details = '';
        $order_detail = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
        	
        	if($row['payment_method'] == 'PayPal'):
        		$payment_details = '
Transaction Id: '.$row['txnid'].'<br>
        		';
        	elseif($row['payment_method'] == 'Stripe'):
				$payment_details = '
Transaction Id: '.$row['txnid'].'<br>
Card number: '.$row['card_number'].'<br>
Card CVV: '.$row['card_cvv'].'<br>
Card Month: '.$row['card_month'].'<br>
Card Year: '.$row['card_year'].'<br>
        		';
        	elseif($row['payment_method'] == 'Bank Deposit'):
				$payment_details = '
Transaction Details: <br>'.$row['bank_transaction_info'];
        	endif;

            $order_detail .= '
Customer Name: '.$row['customer_name'].'<br>
Customer Email: '.$row['customer_email'].'<br>
Payment Method: '.$row['payment_method'].'<br>
Payment Date: '.$row['payment_date'].'<br>
Payment Details: <br>'.$payment_details.'<br>
Paid Amount: '.$row['paid_amount'].'<br>
Payment Status: '.$row['payment_status'].'<br>
Shipping Status: '.$row['shipping_status'].'<br>
Payment Id: '.$row['payment_id'].'<br>
            ';
        }

        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {
            $i++;
            $order_detail .= '
<br><b><u>Product Item '.$i.'</u></b><br>
Product Name: '.$row['product_name'].'<br>
Size: '.$row['size'].'<br>
Color: '.$row['color'].'<br>
Quantity: '.$row['quantity'].'<br>
Unit Price: '.$row['unit_price'].'<br>
            ';
        }

        $statement = $pdo->prepare("INSERT INTO tbl_customer_message (subject,message,order_detail,cust_id) VALUES (?,?,?,?)");
        $statement->execute(array($subject_text,$message_text,$order_detail,$_POST['cust_id']));

        // sending email
        $to_customer = $cust_email;
        $message = '
<html><body>
<h3>Message: </h3>
'.$message_text.'
<h3>Order Details: </h3>
'.$order_detail.'
</body></html>
';
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addReplyTo($admin_email ?: SMTP_REPLYTO_EMAIL, SMTP_REPLYTO_NAME);
            $mail->addAddress($to_customer, 'Customer');
            $mail->isHTML(true);
            $mail->Subject = $subject_text;
            $mail->Body    = $message;
            $mail->send();
            $success_message = 'Your email to customer is sent successfully.';
        } catch (Exception $e) {
            $error_message .= 'Mailer Error: ' . $e->getMessage() . "\n";
        }

    }
}
?>
<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Orders</h1>
	</div>
	<div class="content-header-right">
		<a href="order-add.php" class="btn btn-primary btn-sm">Add New</a>
	</div>
</section>


<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Totals</th>
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT * FROM tbl_payment ORDER by id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                            ?>
                            <tr class="<?php echo ($row['payment_status'] == 'Pending') ? 'bg-r' : 'bg-g'; ?>">
                                <td><?php echo $i; ?></td>
                                <td>
                                    <b>Invoice:</b> <?php echo htmlspecialchars($row['payment_id']); ?><br>
                                    <b>Name:</b> <?php echo htmlspecialchars($row['customer_name']); ?><br>
                                    <b>Email:</b> <?php echo htmlspecialchars($row['customer_email']); ?><br>
                                    <b>Phone:</b> <?php echo htmlspecialchars($row['customer_phone'] ?? ''); ?><br>
                                    <b>Date:</b> <?php echo htmlspecialchars($row['payment_date']); ?><br><br>
                                    <a href="#" data-toggle="modal" data-target="#model-<?php echo $i; ?>" class="btn btn-warning btn-xs" style="width:50%;margin-bottom:4px;">Send Message</a>
                                    <div id="model-<?php echo $i; ?>" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title" style="font-weight: bold;">Send Message</h4>
                                                </div>
                                                <div class="modal-body" style="font-size: 14px">
                                                    <form action="" method="post">
                                                        <input type="hidden" name="cust_id" value="<?php echo $row['customer_id']; ?>">
                                                        <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <td>Subject</td>
                                                                <td><input type="text" name="subject_text" class="form-control" style="width: 100%;"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Message</td>
                                                                <td><textarea name="message_text" class="form-control" cols="30" rows="10" style="width:100%;height: 200px;"></textarea></td>
                                                            </tr>
                                                            <tr>
                                                                <td></td>
                                                                <td><input type="submit" value="Send Message" name="form1"></td>
                                                            </tr>
                                                        </table>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                                    $statement1->execute(array($row['payment_id']));
                                    $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result1 as $row1) {
                                        echo '<b>' . htmlspecialchars($row1['product_name']) . '</b><br>';
                                        echo 'Size: ' . htmlspecialchars($row1['size']) . ' | Color: ' . htmlspecialchars($row1['color']) . '<br>';
                                        echo 'Qty: ' . htmlspecialchars($row1['quantity']) . ' | Price: ' . htmlspecialchars($row1['unit_price']) . '<br><br>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <b>Subtotal:</b> <?php echo number_format((float)($row['subtotal'] ?? 0), 2); ?><br>
                                    <b>Discount:</b> <?php echo number_format((float)($row['discount_amount'] ?? 0), 2); ?><br>
                                    <b>VAT:</b> <?php echo number_format((float)($row['vat_amount'] ?? 0), 2); ?><br>
                                    <b>Grand Total:</b> <?php echo number_format((float)($row['grand_total'] ?? 0), 2); ?><br>
                                    <b>Due:</b> <?php echo number_format((float)($row['due_amount'] ?? 0), 2); ?>
                                </td>
                                <td>
                                    <b>Method:</b> <?php echo htmlspecialchars($row['payment_method']); ?><br>
                                    <b>Paid:</b> <?php echo number_format((float)($row['paid_amount'] ?? 0), 2); ?><br>
                                    <b>Status:</b> <?php echo htmlspecialchars($row['payment_status']); ?><br>
                                    <b>Shipping:</b> <?php echo htmlspecialchars($row['shipping_status']); ?><br>
                                </td>
<td style="white-space: nowrap;">
    <a href="invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-info" style="margin-right:5px;">Invoice</a>
    <a href="order-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="margin-right:5px;">Edit</a>
    <a href="#" class="btn btn-danger"
       data-href="order-delete.php?id=<?php echo $row['id']; ?>"
       data-toggle="modal"
       data-target="#confirm-delete">Delete</a>
</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                Sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>