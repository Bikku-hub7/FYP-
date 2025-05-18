<?php 

include('server/connection.php');

//use the search section
if(isset($_POST['search'])){

    $category = $_POST['category'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_category = ? AND product_price <= ?");
    $stmt->bind_param("si", $category, $price);
    $stmt->execute();
    $products = $stmt->get_result();

    //return all products;
}else{
  $stmt = $conn->prepare("SELECT * FROM products");
  $stmt->execute();
  $products = $stmt->get_result();

}


?>

<?php

include('layouts/header.php');

?>


    <style>
      .product img{
        width: 100%;
        height: auto;
        box-sizing: border-box;
        object-fit: cover;
      
      }
      .pagination a{
        color: red;
      }
      .pagination li:hover a{
        color: white;
        background-color: red;
        font-weight: bold !important;
      }
      
    </style>


        <!--Search-->
        <div class="container mt-5">
    <div class="row">
        <!-- Left Sidebar (Filter Section) -->
        <aside class="col-lg-3 col-md-4">
            <section id="search" class="my-5 py-5">
                <div class="container">
                    <p><strong>Search Bikes</strong></p>
                    <hr>
                </div>

                <form action="bikeslist.php" method="POST">
                    <div class="mb-3">
                        <p><strong>Bikes Brand</strong></p>
                        <div class="form-check">
                            <input class="form-check-input" value="Ducati" type="radio" name="category" id="category_one">
                            <label class="form-check-label" for="category_one">Ducati</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" value="BMW" type="radio" name="category" id="category_two">
                            <label class="form-check-label" for="category_two">BMW</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" value="Yamaha" type="radio" name="category" id="category_three">
                            <label class="form-check-label" for="category_three">Yamaha</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" value="Honda" type="radio" name="category" id="category_four">
                            <label class="form-check-label" for="category_four">Honda</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" value="Aprilia" type="radio" name="category" id="category_five">
                            <label class="form-check-label" for="category_five">Aprilia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" value="TVS" type="radio" name="category" id="category_five">
                            <label class="form-check-label" for="category_six">TVS</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Price Range</strong></p>
                        <input type="range" class="form-range w-100" name="price" min="1" max="1000" id="priceRange" oninput="updatePrice()">
                        <div class="d-flex justify-content-between">
                            <span>NPR-<span id="minPrice">1</span></span>
                            <span>NPR-<span id="currentPrice">500</span></span>
                            <span>NPR-<span id="maxPrice">1000</span></span>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <input type="submit" name="search" class="btn btn-primary w-100" value="Search">
                    </div>
                </form>
            </section>
        </aside>

        <!-- Right Section (Bikes List) -->
        <main class="col-lg-9 col-md-8">
            <section id="featured" class="my-5 py-5">
                <div class="container">
                    <h3>Our Bikes</h3>
                    <hr>
                    <p>Check out our bikes by brand</p>
                </div>

                <div class="row">
                    <?php while($row = $products->fetch_assoc()) { ?>
                        <div onclick="window.location.href='single_product.php';" class="product text-center col-lg-3 col-md-4 col-sm-6">
                            <img class="img-fluid mb-3" src="uploads/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" />
                            <div class="star">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <h5 class="p-name"><?php echo $row['product_name']; ?></h5>
                            <h4 class="p-price">NPR-<?php echo $row['product_price']; ?></h4>
                            <a class="btn shop-buy-btn" href="single_product.php?product_id=<?php echo $row['product_id']; ?>">Buy Now</a>
                        </div>
                    <?php } ?>
                </div>
                <script>
                  function updatePrice() {
                      let priceRange = document.getElementById("priceRange");
                      let currentPrice = document.getElementById("currentPrice");
                      currentPrice.innerText = priceRange.value;
                  }
                </script>
            </section>
        </main>
    </div>
</div>



               
              <nav aria-label="Page navigation example" class="max-auto">
                <ul class="pagination mt-5 max-auto">
                  <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
              </nav>
            </div>    
            </section>

<?php 

include('layouts/footer.php');

?>