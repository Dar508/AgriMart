<?php

// Handle Add to Wishlist
if (isset($_POST['add_to_wishlist'])) {

   if (empty($user_id)) {
      header('location:user_login.php');
      exit();
   } else {
      $pid = filter_var($_POST['pid'] ?? 0, FILTER_VALIDATE_INT);

      if (!$pid) {
         $message[] = 'Invalid product selected!';
      } else {
         try {
            // Fetch trusted product details from DB instead of trusting POST body
            $select_product = $conn->prepare("SELECT name, price, image_01 FROM `products` WHERE id = ?");
            $select_product->execute([$pid]);

            if ($select_product->rowCount() > 0) {
               $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
               $name  = $fetch_product['name'];
               $price = $fetch_product['price'];
               $image = $fetch_product['image_01'];

               // Check if item is already in user's wishlist using pid
               $check_wishlist = $conn->prepare("SELECT id FROM `wishlist` WHERE pid = ? AND user_id = ?");
               $check_wishlist->execute([$pid, $user_id]);

               if ($check_wishlist->rowCount() > 0) {
                  $message[] = 'Already added to wishlist!';
               } else {
                  // Insert into wishlist
                  $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
                  $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);

                  // If moved from cart to wishlist, remove item from cart
                  if (isset($_POST['cart_id'])) {
                     $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
                     $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
                     $delete_cart->execute([$cart_id, $user_id]);
                     $message[] = 'Moved to wishlist!';
                  } else {
                     $message[] = 'Added to wishlist!';
                  }
               }
            } else {
               $message[] = 'Product no longer exists!';
            }
         } catch (PDOException $e) {
            $message[] = 'Database error occurred. Please try again.';
         }
      }
   }
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {

   if (empty($user_id)) {
      header('location:user_login.php');
      exit();
   } else {
      $pid = filter_var($_POST['pid'] ?? 0, FILTER_VALIDATE_INT);
      $qty = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_INT);
      $qty = ($qty && $qty > 0) ? $qty : 1; // Fallback to 1 if negative or invalid

      if (!$pid) {
         $message[] = 'Invalid product selected!';
      } else {
         try {
            // Fetch trusted product details from DB
            $select_product = $conn->prepare("SELECT name, price, image_01 FROM `products` WHERE id = ?");
            $select_product->execute([$pid]);

            if ($select_product->rowCount() > 0) {
               $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
               $name  = $fetch_product['name'];
               $price = $fetch_product['price'];
               $image = $fetch_product['image_01'];

               // Check if item is already in user's cart
               $check_cart = $conn->prepare("SELECT id FROM `cart` WHERE pid = ? AND user_id = ?");
               $check_cart->execute([$pid, $user_id]);

               if ($check_cart->rowCount() > 0) {
                  $message[] = 'Already added to cart!';
               } else {
                  // Remove from wishlist if present before adding to cart
                  $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ? AND user_id = ?");
                  $delete_wishlist->execute([$pid, $user_id]);

                  $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
                  $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
                  $message[] = 'Added to cart!';
               }
            } else {
               $message[] = 'Product no longer exists!';
            }
         } catch (PDOException $e) {
            $message[] = 'Database error occurred. Please try again.';
         }
      }

   }

}

?>