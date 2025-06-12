<?php

@include 'config.php';

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (Place at the top, before any logic)
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
}

// Email sending code when order status is completed
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];

    // Get the payment method from the order
    $order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$order_id'") or die('Query failed');
    $order_data = mysqli_fetch_assoc($order_query);
    $method = $order_data['method'];
    $customer_email = $order_data['email'];
    $customer_name = $order_data['name'];

    // Set payment status to 'pending' if the method is 'cash on delivery' and status is 'paid'
   //  if ($method === 'cash on delivery' && $update_payment === 'paid') {
   //      $update_payment = 'pending';
   //  }

    // Update payment status in the database
    mysqli_query($conn, "UPDATE orders SET payment_status = '$update_payment' WHERE id = '$order_id'") or die('Query failed');
    $message[] = 'Order status has been updated!';

    // Send an email if the status is manually set to "completed"
    if ($update_payment == 'completed') {
        $mail = new PHPMailer(true);

        try {
            // SMTP Server Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sathishix01@gmail.com'; // Your Gmail address
            $mail->Password = 'dwpq lgiu qvyw crxw'; // Use an App Password, not your regular Gmail password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Email Content
            $mail->setFrom('sathishix01@gmail.com', 'Dynamic Flower Shop');
            $mail->addAddress($customer_email, $customer_name);
            $mail->Subject = 'Order Successfully Delivered!';
            $mail->Body = "Hi $customer_name,\n\nYour order has been successfully delivered. Thank you for shopping with us!\n\nBest Regards,\nDynamic Flower Shop Team";

            $mail->send();
            $message[] = "Email sent to $customer_email.";
        } catch (Exception $e) {
            $message[] = "Failed to send email. Error: {$mail->ErrorInfo}";
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM orders WHERE id = '$delete_id'") or die('query failed');
    header('location:admin_orders.php');
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom Admin CSS File Link -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<section class="placed-orders">

   <h1 class="title">Placed Orders</h1>

   <div class="box-container">

      <?php
      $select_orders = mysqli_query($conn, "SELECT * FROM orders") or die('query failed');
      if (mysqli_num_rows($select_orders) > 0) {
         while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
      ?>
      <div class="box">
         <p> User ID : <span><?php echo $fetch_orders['user_id']; ?></span> </p>
         <p> Placed On : <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
         <p> Name : <span><?php echo $fetch_orders['name']; ?></span> </p>
         <p> Number : <span><?php echo $fetch_orders['number']; ?></span> </p>
         <p> Email : <span><?php echo $fetch_orders['email']; ?></span> </p>
         <p> Address : <span><?php echo $fetch_orders['address']; ?></span> </p>
         <p> Total Products : <span><?php echo $fetch_orders['total_products']; ?></span> </p>
         <p> Total Price : <span>₹<?php echo $fetch_orders['total_price']; ?>/-</span> </p>
         <p> Payment Method : <span><?php echo $fetch_orders['method']; ?></span> </p>
         <form action="" method="post">
            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
            <select name="update_payment">
               <option disabled selected>
               <?php 
                // Display 'pending' for COD instead of 'paid'
               if ($fetch_orders['method'] === 'cash on delivery' && $fetch_orders['payment_status'] === 'paid') {
                     echo 'pending';
                } else {
                     echo $fetch_orders['payment_status'];
               }
                ?>
               </option>
               <option value="pending">Pending</option>
               <option value="completed">Completed</option>
               <!-- <option value="failed">Failed</option> -->
            </select>



            <input type="submit" name="update_order" value="Update" class="option-btn">
            <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
         </form>
      </div>
      <?php
         }
      } else {
         echo '<p class="empty">No orders placed yet!</p>';
      }
      ?>
   </div>

</section>

<script src="js/admin_script.js"></script>

</body>
</html>