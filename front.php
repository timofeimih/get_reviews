<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>

	<link rel="stylesheet" href="style.css?v=0.58">
	
	<link rel="stylesheet" type="text/css" href="./slick/slick.css"/>

	<script
	src="https://code.jquery.com/jquery-2.2.4.min.js"
	integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
	crossorigin="anonymous"></script>
	<script type="text/javascript" src="./slick/slick.min.js"></script>

	<style>
		.slick-prev:before, .slick-next:before{
			font-family: monospace;
		}
		.review-text__content{
			overflow: auto;
		}
	</style>

	 
</head>
<body>
	


<section class="reviews-widget">
    <div class="reviews-widget__inner">
        <div class="reviews-widget__body">
        	<div class="reviews-widget__container">
	        	<?php 
	        	function clean($string) {
				   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

				   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
				}
				
	        	if(isset($_GET['f'])):
	        		$f = clean($_GET['f']);
	        		$data_json = file_get_contents($f . '.json');
	        		$data_json = json_decode($data_json);
	        	
		        	foreach ($data_json as $key => $item): 
		        		$date = date('d.m.Y', $item->timestamp);
		        		?>
		        		<div class="review">
		                    <div class="review-header">
		                        <div class="review-header__source">
		                            <div class="review-header__source-logo">
		                                <img src="img/<?php echo $item->service ?>.svg?v=2" width="36px" height="36px">
		                            </div>
		                            <div class="review-header__source-text">
		                                <div class="review-header__source-caption">
		                                    <a href="<?php echo $item->link ?>" target="_blank"><?php echo $item->service_full ?><i class="icn external-link"></i></a>
		                                    <div class="review-rating review-rating-<?php echo $item->stars ?>"></div>
		                                </div>
		                                <div class="review-header__author"><?php echo $item->name ?><span class="review-header__time"> - <?php echo $date ?></span></div>
		                            </div>
		                        </div>
		                    </div>
		                    <div class="review-body">
		                        <div class="review-text">
		                            <div class="review-text__content">
		                                <?php echo $item->review ?>
		                            </div>

		                        </div>
		                    </div>
		                    <div class="review-footer review-footer--main">
		                        <div class="review-footer__info">
		                            <div class="review-footer__filial">
		                                <i class="icn map-marker-radius"></i>
		                                <div class="review-footer__filial-info">
		                                    <div class="review-footer__filial-name"><?php echo $item->office_name ?></div>
		                                </div>
		                            </div>
		                        </div>
		                    </div>
		                </div>
		        	<?php endforeach;
		        endif;
	        	 ?>
	       	</div>
        </div>

    </div>
</section>


<script>

	$('.reviews-widget__container').slick({

	  infinite: true,
	  centerPadding: '100px',
	  slidesToShow: 4,
	  slidesToScroll: 3, 
	  rows: 1,
	  responsive: [
	  	{
	      breakpoint: 1024,
	      settings: {
	        arrows: false,
	        centerMode: true,
	        centerPadding: '40px',
	        slidesToShow: 2,
			slidesToScroll: 1
	      }
	    },
	    {
	      breakpoint: 768,
	      settings: {
	        arrows: false,
	        centerMode: true,
	        centerPadding: '40px',
	        slidesToShow: 1,
			slidesToScroll: 1
	      }
	    },
	    {
	      breakpoint: 480,
	      settings: {
	        arrows: false,
	        centerMode: true,
	        centerPadding: '40px',
	        slidesToShow: 1
	      }
	    }
	  ]
	});
 </script>

</body>
</html>