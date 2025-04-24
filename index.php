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
                  <h4 class="p-price">$ <?php echo $row['product_price']; ?> </h4>
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
                  <h4 class="p-price">$ <?php echo $row['product_price']; ?> </h4>
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
                  <h4 class="p-price">$ <?php echo $row['product_price']; ?> </h4>
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
                  <h4 class="p-price">$ <?php echo $row['product_price']; ?> </h4>
                  <a href="<?php echo"single_product.php?product_id=". $row['product_id']; ?>"><button class="buy-btn">Buy Now</button></a>
                </div>
                <?php } ?>
              </div>
          </section>    


<?php 

include('layouts/footer.php');

?>
