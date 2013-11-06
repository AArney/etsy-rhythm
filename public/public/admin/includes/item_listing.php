<?php ?>



    <div class="etsy-item-container" id="<?php echo $listing_id; ?>">
    
		<a title="<?php echo $title; ?>" href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="etsy-item-thumbnail-link">
			<img alt="<?php echo  $title; ?>" src="<?php echo $url_170x135; ?>" class="etsy-item-thumbnail"> 
        </a>  
		<p class="etsy-item-title">
			<a title="<?php echo $title; ?>" href="<?php echo $url; ?>" target="<?php echo  $target; ?>"><?php echo $title; ?></a>
		</p>
		<p class="etsy-item-availability">
			<a title="<?php echo $title; ?>" href="<?php echo  $url; ?>" target="<?php echo $target; ?>"><?php echo $state; ?></a>
        </p>
		<p class="etsy-item-price"><?php echo $price; ?><span class="etsy-item-currency-code"><?php echo $currency_code; ?></span></p>
	</div>
<?php ?>