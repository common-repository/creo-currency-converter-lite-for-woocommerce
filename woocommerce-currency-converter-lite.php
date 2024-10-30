<?php
/**
 * Plugin Name: Creo Currency Converter Lite for Woocommerce
 * Author: Rashedamin
 * Author URI: https://profiles.wordpress.org/rashedamin
 * Description: Show an alternative currency beside the actual price of your product. 
 * Version: 1.0.0
 * Text Domain: woocommerce-currency-converter-lite (/languages)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'plugins_loaded', 'creo_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function creo_load_textdomain() {
  load_plugin_textdomain( 'woocommerce-currency-converter-lite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

function creo_get_exchange($from,$to,$amount)
{
	$convValue = $amount;
	$dom_doc = new DOMDocument();
	$url="http://www.google.com/finance/converter?a=".$amount."&from=".$from."&to=".$to;
	if(@file_get_contents($url)){
	$content = @file_get_contents($url);
	 }
	 else 
	 {
	return "Please Connect to Internet first";
	}
	libxml_use_internal_errors(true);
	$dom_doc->loadHTML($content);
	libxml_clear_errors();
	$elements = $dom_doc->getElementsByTagName('span');
	if (!is_null($elements)) {
	  foreach ($elements as $element) {
	    $nodes = $element->childNodes;
	    foreach ($nodes as $node) {
	      $convValue = str_replace($to, '', $node->nodeValue);
	    }
	  }
	}

	return $convValue;
}

add_filter( 'woocommerce_get_price_html', 'creo_replace_price', 10, 2 );
 
function creo_replace_price( $price ) {
	$product = wc_get_product( get_the_ID() );
	if( $product->is_type( 'simple' ) )
	{
		// buy the pro version for sales price conversion!
		$orig_price = get_post_meta( get_the_ID(), '_regular_price',true);

		 if(!isset($_COOKIE["selected"])){
			$cust_price = creo_get_exchange(get_woocommerce_currency(),'EUR',$orig_price);
			$price = $price ." (EUR ". $cust_price.")";
			if(!is_admin())return $price;
			else return $orig_price;
		}
		$cust_price = creo_get_exchange(get_woocommerce_currency(),$_COOKIE["selected"],$orig_price);
		$price = $price ." (".$_COOKIE["selected"]." ". $cust_price.")";
		if(!is_admin())return $price;
		else return get_woocommerce_currency_symbol().$orig_price;
	}
	elseif( $product->is_type( 'variable' ) ){
		// buy the pro version for variable price conversion!
		return get_post_meta( get_the_ID(), '_regular_price',true);
	}
	
	
	
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'creo_action_links' );

function creo_action_links( $links ) {
   $links[] = '<a href="http://codecanyon.net/item/creo-currency-converter-pro-for-woocommerce/12892442" target="_blank">Purchase Pro</a>';
   return $links;
}

// Widget Class
class Creo_Currency_Converter extends WP_Widget {
    public function __construct() {
        $widget_ops = array( 'classname' => 'Creo_Currency_Converter', 'description' => __( "Show alternative currrencies beside your products' actual price.","woocommerce-currency-converter-lite") );
        parent::__construct('Creo_Currency_Converter', __('Currency Converter Lite','woocommerce-currency-converter-lite'), $widget_ops);
    }

    /**
    * Widget 
	* @since 1.0 
	* @param $args: inputs to widget
	*	   $instance: instance of widget (on change) 	 
    */
    public function widget( $args, $instance ) { 
        echo $args['before_widget'];
        // < 157 Currencies in Lite
        $all_currencies = array('AFN'=>'Afghanistan Afghani','ALL'=>'Albania Lek','AMD'=>'Armenia Dram','DZD'=>'Algeria Dinar','AOA'=>'Angola Kwanza','ARS'=>'Argentina Peso','AUD'=>'Australian Dollar','AWG'=>'Aruba Guilder','AZN'=>'Azerbaijan New Manat','BAM'=>'Bosnia and Herzegovina Convertible Marka','BBD'=>'Barbados Dollar','BDT'=>'Bangladesh Taka','XAF'=>'Communauté Financière Africaine (BEAC) CFA Franc BEAC','KHR'=>'Cambodia Riel','KMF'=>'Comoros Franc','HRK'=>'Croatia Kuna','DJF'=>'Djibouti Franc','DKK'=>'Denmark Krone','DOP'=>'Dominican Republic Peso','EGP'=>'Egypt Pound','ERN'=>'Eritrea Nakfa','SVC'=>'El Salvador Colon','XCD'=>'East Caribbean Dollar','ETB'=>'Ethiopia Birr','EUR'=>'Euro Member Countries','FJD'=>'Fiji Dollar','FKP'=>'Falkland Islands (Malvinas) Pound','GEL'=>'Georgia Lari','PEN'=>'Peru Nuevo Sol','PGK'=>'Papua New Guinea Kina','PHP'=>'Philippines Peso','PKR'=>'Pakistan Rupee','PLN'=>'Poland Zloty','SGD'=>'Singapore Dollar','SZL'=>'Swaziland Lilangeni','THB'=>'Thailand Baht');	
            	?>
            <div class="tiny">
            	<h5><?php _e("Select Alternative Currency","woocommerce-currency-converter-lite")?></h5> 
				<select class="ui fluid selection dropdown" name="currency_dd" id="currency_dd">
					<div class="menu">
					  <?php if(isset($_COOKIE["selected"])) { ?>
					  <option class="item" selected="selected"><?php echo $all_currencies[$_COOKIE["selected"]]." (".$_COOKIE["selected"].")"; ?></option>
					  <?php } else { ?>
					  <option class="item" selected="selected">Search</option>
					  <?php } ?>
					  <?php
					    foreach($all_currencies as $key=>$value) { ?>
					    <?php if($_COOKIE["selected"] === $key) continue; ?>
					      <option class="item" value="<?php echo $key ?>"><?php echo $value." (".$key.")"?></option>
					  <?php
					    } ?>
					</div>
				</select>
			</div>

				
<!-- semantic ui (c) dropdown  -->
<script type="text/javascript">
(function($) {
$(document)
    .ready(function() {
      $('.ui.dropdown')
        .dropdown({
          on: 'click'
        });
    })
  ;
  })( jQuery );
</script>
<script type='text/javascript'>
/* <![CDATA[ */
(function() {

	function setCookie(cname,cvalue,exdays) {
    	document.cookie = cname + "=" + cvalue + ";path=/";
	}

    var dropdown = document.getElementById( "currency_dd" );
    function onChoiceChange() {
        if ( dropdown.options[ dropdown.selectedIndex ].value) {
            location.reload();
        }
    }
    function onChange(){
    	setCookie("selected",dropdown.options[ dropdown.selectedIndex ].value, 7);
    	onChoiceChange();
    }
    dropdown.onchange = onChange;
})();
/* ]]> */
</script>

<?php
        echo $args['after_widget'];
    }
    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
    /**
     * @param array $instance
     */
    public function form( $instance ) {
        //Defaults
?>
     <br>   
<?php
    }
}
 // class Creo_Currency_Converter widget ends

  function creo_register_currency_widget() {
    register_widget( 'Creo_Currency_Converter' );
}
add_action( 'widgets_init', 'creo_register_currency_widget' );

} // if (woocomerce_exists ends)


add_action( 'wp_head', 'creo_preload_scripts' );

function creo_preload_scripts(){
   	wp_enqueue_script('jquery');
	wp_enqueue_style( 'semantic_ui_dd_style', plugin_dir_url( __FILE__ ) .'libs/dropdown.css' );
	wp_enqueue_style( 'semantic_ui_tr_style', plugin_dir_url( __FILE__ ) .'libs/transition.css' );
	wp_enqueue_style( 'semantic_ui_ip_style', plugin_dir_url( __FILE__ ) .'libs/input.css' );
	wp_enqueue_style( 'semantic_ui_lb_style', plugin_dir_url( __FILE__ ) .'libs/label.css' );
	wp_enqueue_style( 'currency_chooser_style', plugin_dir_url( __FILE__ ) .'css/style.css' );
	wp_enqueue_script( 'semantic_ui_dd_script', plugin_dir_url( __FILE__ ) .'libs/dropdown.js' , array('jquery'));
	wp_enqueue_script( 'semantic_ui_tr_script', plugin_dir_url( __FILE__ ) .'libs/transition.js', array('jquery'));
}