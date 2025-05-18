<?php

include('layouts/header.php');

?>

      <!-- Home -->
       <section id="home">
        <div class="container">
            <h5> BIKE AVAILABLE AT</h5>
            <h1><span>Best Price</span> This Season</h1>
            <p>Ready to ride? Rent your dream bike today and hit the road with ease!</p>
            <button>Shop Now</button>
            </div> 
       </section>


        <!--New-->

        <section id="new" class="w-100">
          <div class="container text-center mt-5 py-5">
            <h3>Find the Best Bike For You</h3>
            <hr class="mx-auto">
            <p> You will be able to fully enjoy your holiday and your ride! Any problems? Our passionate team will be happy to help you!! No waste of time during your holidays to find a rental point on the spot! No language barrier, thanks to our multilingual team! At the same price you would pay on the spot! We have best bikes with best deals</p>
            </div>
          <div class="row p-0 m-0 d-flex justify-content-between">
              <!-- One -->
              <div class="one col-lg-4 col-md-4 col-sm-12 p-0">
                  <img class="img-fluid" src="assets/image/p1.jpg" alt="Mountain Bike 1"/> 
                  <div class="details text-center">
                      <h2>Get Your</h2>
                  </div>
              </div>
              <!-- Two -->
              <div class="one col-lg-4 col-md-4 col-sm-12 p-0">
                  <img class="img-fluid" src="assets/image/p2.jpg" alt="Mountain Bike 2"/> 
                  <div class="details text-center">
                      <h2>Favorite Bikes</h2>
                      <h5 class="p-name">Sports Bikes</h5>
                  </div>
              </div>
              <!-- Three -->
              <div class="one col-lg-4 col-md-4 col-sm-12 p-0">
                  <img class="img-fluid" src="assets/image/p3.jpg" alt="Mountain Bike 3"/> 
                  <div class="details text-center">
                      <h2>At Reasonable Price</h2>
                  </div>
              </div>
          </div>
      </section>
      

         <!--Brand-->
            <section id="brand">
              <div class="container text-center mt-5 py-5">
                <h3>Top Brands</h3>
                <hr class="mx-auto">
                <p> Discover a wide range of motorcycles from leading brands like Yamaha, Honda, Ducati, Aprilia, TVS and many more. Whether you seek speed, adventure, or daily commuting, our collection offers high-performance, stylish, and reliable bikes to match every rider's needs. Ride with confidence and quality!🔥</p>
                </div>
                <div class="container">
                    <div class="row">
                        <img class="'img-fluid col-lg-3 col-md-6 col-sm-12" src="assets/image/brand2.png"/>
                        <img class="'img-fluid col-lg-3 col-md-6 col-sm-12" src="assets/image/brand1.png"/>
                        <img class="'img-fluid col-lg-3 col-md-6 col-sm-12" src="assets/image/brand3.png"/>
                        <img class="'img-fluid col-lg-3 col-md-6 col-sm-12" src="assets/image/brand4.png"/>
                        <img class="'img-fluid col-lg-3 col-md-6 col-sm-12" src="assets/image/brand5.png"/>
                    </div>
                </div>
            </section>

          <!--Banner-->
          <section id="banner" class="my-5 py-5">
            <div class="container">
              <h4>Get the Best Deals</h4>
              <h1>Autum Collection<br>UP to <span>30% OFF</span> </h1>
              <button class="text-uppercase">Shop Now</button>
              </div>
              </section>

              
          <!--Featured-->
                    <!--Yamaha-->
                    <section id="featured" class="my-5 pb-5">
            <div class="container text-center mt-5 py-5">
              <h3>Yamaha</h3>
              <hr class="mx-auto">
              <p>Renowned for performance, innovation, and reliability, Yamaha offers a diverse range of bikes, from sportbikes and cruisers to adventure and commuter models. Designed for speed, comfort, and cutting-edge technology, Yamaha motorcycles deliver an exceptional riding experience for every enthusiast. </p>
              </div>
              <div class="row mx-auto container-fluid">

              <?php include('server/get_yamaha.php'); ?>
              <?php while($row = $yamaha_Products->fetch_assoc()){ ?>
                <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
                  <img class="img-fluid mb-3" src="uploads/<?php echo $row['product_image']; ?>"/>
                  <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h5 class="p-name"><?php echo $row['product_name']; ?></h5>
                  <h4 class="p-price">NPR- <?php echo $row['product_price']; ?> </h4>
                  <a href="<?php echo"single_product.php?product_id=". $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>
                </div>
                <?php } ?>
              </div>
          </section>

          <!--Ducati-->
          <section id="featured" class="my-5 pb-5">
            <div class="container text-center mt-5 py-5">
              <h3>Ducati</h3>
              <hr class="mx-auto">
              <p>Synonymous with Italian craftsmanship and performance, Ducati offers high-performance motorcycles with cutting-edge technology, sleek designs, and exhilarating power. From superbikes to adventure and naked bikes, Ducati delivers an unmatched riding experience for enthusiasts who crave speed and style.</p>
              </div>
              <div class="row mx-auto container-fluid">

              <?php include('server/get_ducati.php'); ?>
              <?php while($row = $ducati_Products->fetch_assoc()){ ?>
                <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
                  <img class="img-fluid mb-3" src="uploads/<?php echo $row['product_image']; ?>"/>
                  <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h5 class="p-name"><?php echo $row['product_name']; ?></h5>
                  <h4 class="p-price">NPR- <?php echo $row['product_price']; ?> </h4>
                  <a href="<?php echo"single_product.php?product_id=". $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>
                </div>
                <?php } ?>
              </div>
          </section>


          <!--Honda-->
          <section id="featured" class="my-5 pb-5">
            <div class="container text-center mt-5 py-5">
              <h3>Honda</h3>
              <hr class="mx-auto">
              <p>Known for reliability, innovation, and performance, Honda offers a diverse lineup, from fuel-efficient commuters to high-performance sportbikes and adventure tourers. With cutting-edge technology and superior engineering, Honda motorcycles provide a smooth, safe, and exhilarating ride for all types of riders.</p>
              </div>
              <div class="row mx-auto container-fluid">

              <?php include('server/get_honda.php'); ?>
              <?php while($row = $honda_Products->fetch_assoc()){ ?>
                <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
                  <img class="img-fluid mb-3" src="uploads/<?php echo $row['product_image']; ?>"/>
                  <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h5 class="p-name"><?php echo $row['product_name']; ?></h5>
                  <h4 class="p-price">NPR- <?php echo $row['product_price']; ?> </h4>
                  <a href="<?php echo"single_product.php?product_id=". $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>
                </div>
                <?php } ?>
              </div>
          </section>  
          
          <!--Aprilia-->
          <section id="featured" class="my-5 pb-5">
            <div class="container text-center mt-5 py-5">
              <h3>Aprilia</h3>
              <hr class="mx-auto">
              <p>A blend of Italian engineering and racing heritage, Aprilia is known for high-performance sportbikes, cutting-edge technology, and bold designs. Whether on the track or the streets, Aprilia motorcycles deliver precision handling, power, and an adrenaline-fueled riding experience.</p>
              </div>
              <div class="row mx-auto container-fluid">

              <?php include('server/get_aprilia.php'); ?>
              <?php while($row = $aprilia_Products->fetch_assoc()){ ?>
                <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
                  <img class="img-fluid mb-3" src="uploads/<?php echo $row['product_image']; ?>"/>
                  <div class="star">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <h5 class="p-name"><?php echo $row['product_name']; ?></h5>
                  <h4 class="p-price">NPR- <?php echo $row['product_price']; ?> </h4>
                  <a href="<?php echo"single_product.php?product_id=". $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>
                </div>
                <?php } ?>
              </div>
          </section>    

<!-- Chat Support Widget -->
<div id="chat-support" style="position: fixed; bottom: 20px; right: 20px; font-family: sans-serif;">
    <div id="chat-toggle" style="background-color: rgb(255,0,0); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;">
         <i class="fas fa-comments fa-2x text-white"></i>
    </div>
    <div id="chat-box" style="display: none; background: #fff; border: 1px solid red; border-radius: 15px; width: 300px; height: 400px; margin-top: 10px; flex-direction: column;">
         <!-- Chat Header -->
         <div id="chat-header" style="background-color: rgb(242,24,24); color: #fff; padding: 10px; font-weight: bold;">
              Bikku Bike Rental
         </div>
         <!-- Chat Messages Area -->
         <div id="chat-messages" style="flex: 1; padding: 10px; overflow-y: auto;">
              <p>Hello! How can we assist you today?</p>
              <div id="chat-options">
                  <button class="chat-option" data-response="We offer a variety of bikes from scooters to sport bikes. All rentals come with helmets.">🏍 Rental Info</button>
                  <button class="chat-option" data-response="Prices start at NPR 500/day. Discounts available for longer rentals.">💰 Pricing</button>
                  <button class="chat-option" data-response="You can reach us at 9800000000 or email support@bikkubike.com.">📞 Contact Support</button>
              </div>
         </div>
         <!-- Chat Input Area -->
         <div id="chat-input-area" style="padding: 10px;">
              <form id="chat-form" action="server/store_chat.php" method="post">
                   <input type="text" name="chat_message" placeholder="We will response as soon as possible..." style="width: 100%; padding: 5px;" />
              </form>
         </div>
    </div>
</div>
<script>
    document.getElementById('chat-toggle').addEventListener('click', function() {
       var chatBox = document.getElementById('chat-box');
       var display = window.getComputedStyle(chatBox).display;
       chatBox.style.display = (display === 'none') ? 'flex' : 'none';
    });

    // Updated: Hide sibling options and display response for the clicked button.
    document.querySelectorAll('.chat-option').forEach(function(button) {
      button.addEventListener('click', function() {
         var response = this.getAttribute('data-response');
         var optionsContainer = document.getElementById('chat-options');
         // Hide all other options, keep the clicked one.
         Array.from(optionsContainer.children).forEach(function(btn) {
             if (btn !== button) {
                 btn.style.display = 'none';
             }
         });
         // Append response after the clicked button if not already added.
         if (!button.nextElementSibling || button.nextElementSibling.tagName.toLowerCase() !== 'p') {
             var responseElem = document.createElement('p');
             responseElem.textContent = response;
             button.parentNode.appendChild(responseElem);
         }
      });
    });

    // Add event listener for chat form submission to store the input via AJAX
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const chatMessage = this.chat_message.value;
        if(chatMessage.trim()){
           fetch('server/store_chat.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
             body: 'chat_message=' + encodeURIComponent(chatMessage)
           })
           .then(response => response.text())
           .then(data => {
              console.log(data);
              this.chat_message.value = '';
           })
           .catch(console.error);
        }
    });
</script>

<!-- Added styles for chat UI -->
<style>
    .chat-option {
        background: transparent;
        color: rgba(0, 0, 0, 0.7);
        border-radius: 20px;
        padding: 5px 10px;
        margin: 5px;
    }
</style>
<?php 

include('layouts/footer.php');

?>
