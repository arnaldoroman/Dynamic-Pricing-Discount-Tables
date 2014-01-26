<?php
/*
/**
 * Plugin Name: Dynamic Pricing Discount Tables
 * Plugin URI: 
 * Description: The Woocommerce Dynamic Pricing Plugin is very flexible, it gives you a powerful system for creating four different types of bulk discounts and price adjustments. However it misses out on the most important ingredient – showing the customer the discounts to entice them to buy the product! This product resolves that issue simply but very effectively.
 * Version: 1.0
 * Author: Fahd Murtaza & Will Cook-Martin
 * Author URI: 
 * License: You should have purchased a license from codecanyon.com
 * Copyright 2013  Fahd Murtaza & Will Cook-Martin

  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

  /**
   * Load styles
  **/

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && in_array( 'woocommerce-dynamic-pricing/woocommerce-dynamic-pricing.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

add_action( 'wp_enqueue_scripts', 'dpt_css_js' );

function dpt_css_js() {
       
        $dpt_styles      = (get_option('dpt_styles') == '' || get_option('dpt_styles') == 'default') ? "dpt_styles" : get_option('dpt_styles');

        wp_enqueue_style( 'dpt_styles_builtin', plugin_dir_url( __FILE__ ) . 'css/'.$dpt_styles.'.css', array(), '0.1', 'screen' );
        
        // Allow end user to do their own CSS to override the existing styles, based on chosen theme

        if ( is_readable( plugin_dir_path( __FILE__ ) . 'wdpt_styles_custom.css' ) ) {
         wp_enqueue_style( 'dpt_styles_custom', plugin_dir_url( __FILE__ ) . 'css/dpt_styles_custom.css', array(), '0.1', 'screen' );
        }
        
        // JS includes  
        wp_enqueue_script( 'ezpz_tooltip_load', plugin_dir_url( __FILE__ ) . 'js/general.js', array( 'jquery' ) );
        wp_enqueue_script( 'ezpz_tooltip', plugin_dir_url( __FILE__ ) . 'js/jquery.ezpz_tooltip.min.js', array( 'jquery' ) );
        
}


  /**
   * Boot up dynamic pricing tables
   */


add_action('woocommerce_single_product_summary', 'display_bulk_discount_table', 15);
add_action('woocommerce_after_shop_loop_item', 'display_bulk_discount_table', 20);
 
function display_bulk_discount_table() {

  global $woocommerce, $post, $product;

  $array_rule_sets = get_post_meta($post->ID, '_pricing_rules', true);

  $_regular_price = get_post_meta($post->ID, '_regular_price', true);

  if ($array_rule_sets && is_array($array_rule_sets) && sizeof($array_rule_sets) > 0){

    $tempstring .= '<div class="bulk-savings-table">';

    $tempstring .= '<span class="bulk-savings-table-title">Bulk Savings</span>';

    $tempstring .= '<table>';

    $tempstring1 .= '<tr><td class="bulk-savings-title">Quantity</td>';

    $tempstring2 .='<tr><td class="bulk-savings-title">Price</td>';

     foreach($array_rule_sets as $pricing_rule_sets)

    {
      $counter=1;
      $total_rules= count($pricing_rule_sets['rules']);

      foreach ( $pricing_rule_sets['rules'] as $key => $value ) {

        if($total_rules==$counter){$class= "class='last'";}else {$class= "";}

        if ($pricing_rule_sets['rules'][$key]['to']) {

          $tempstring1 .= '<td '.$class.'>'.$pricing_rule_sets['rules'][$key]['from']."- ".$pricing_rule_sets['rules'][$key]['to']."</td>";

        } else {

          $tempstring1 .= '<td '.$class.'>'.$pricing_rule_sets['rules'][$key]['from']."+</td>";

        }

          $finalprice = $_regular_price;

          $finalprice = number_format(($finalprice - ($finalprice/100)*$pricing_rule_sets['rules'][$key]['amount']), 2);

          $tempstring2.= '<td '.$class.'><span class="amount">'.get_woocommerce_currency_symbol().''.$finalprice."</span></td>";

           $counter++;
        }

       

      }

      $tempstring1 .= '</tr>';

      $tempstring2 .= '</tr>';

      $tempstring  .= $tempstring1 .$tempstring2;

      $tempstring  .= "</table>";

      $tempstring  .= "</div>";

      echo $tempstring;


  }

}
 
add_filter( 'woocommerce_get_price_html', 'dpt_price_html', 100, 2 );

function dpt_price_html( $finalprice ){

global $woocommerce, $post, $product;
  $array_rule_sets = get_post_meta($post->ID, '_pricing_rules', true);
  $_regular_price = get_post_meta($post->ID, '_regular_price', true);
  if ($array_rule_sets < 1) {
    return $finalprice;
  }
  else {
    foreach($array_rule_sets as $pricing_rule_sets){
      foreach ( $pricing_rule_sets['rules'] as $key => $value ) {
          $finalprice=$_regular_price;
          $finalprice = number_format(($finalprice - ($finalprice/100)*$pricing_rule_sets['rules'][$key]['amount']), 2);
          if($key == count($pricing_rule_sets['rules'])-0) {
              return ''.get_woocommerce_currency_symbol().''.$finalprice.'';
          }   
        }
    }
}
}


  /**
   * Update the format for price dispaly, hide original price and just show the rebated one
   * Add the pricing table to the mix and Show a Bulk Savings link which shows detailed pricing table on mopuse over
   * Finally sum it all and return it to (PRICE column) where the filter is called from on cart page 
   */


add_filter( 'woocommerce_cart_item_price_html', 'woocommerce_cart_item_price_html_update', 100,3 );

function woocommerce_cart_item_price_html_update($product_price, $values, $cart_item_key )
{
  $pid=$values['product_id'];
 
  $price_clean = strip_tags(preg_replace("/<del>.+?<\/del>/i", "", $product_price));

  return $price_clean. dpt_bulk_discount_table($cart_item_key,$pid);
}

  /**
   * Display pricing table
   */


function dpt_bulk_discount_table($cart_item_key,$pd) {
  echo $pid;
  
  echo "<a id=\"tip-target-".$cart_item_key."\" class=\"tip-target show_savings_bulk\">VIEW BULK SAVINGS</a>";

  $array_rule_sets = get_post_meta($pd, '_pricing_rules', true);
   
  $_regular_price = get_post_meta($pd, '_regular_price', true);

  if ($array_rule_sets && is_array($array_rule_sets) && sizeof($array_rule_sets) > 0){

  //$tempstring .= '<div class="bulk-savings-table tip-content savings_tooltip" id="tip-content-"'.$cart_item_key."\">';
        
    $tempstring .= '<div id="tip-content-'.$cart_item_key.'"  class="bulk-savings-table tip-content savings_tooltip">';

    $tempstring .= '<span class="bulk-savings-table-title">Bulk Savings</span>';

    $tempstring .= '<table>';

    $tempstring1 .= '<tr><td class="bulk-savings-title">Quantity</td>';

    $tempstring2 .='<tr><td class="bulk-savings-title">Price</td>';

    foreach($array_rule_sets as $pricing_rule_sets)

    {
      $counter=1;
      $total_rules= count($pricing_rule_sets['rules']);

      foreach ( $pricing_rule_sets['rules'] as $key => $value ) {

        if($total_rules==$counter){$class= "class='last'";}else {$class= "";}

        if ($pricing_rule_sets['rules'][$key]['to']) {

          $tempstring1 .= '<td '.$class.'>'.$pricing_rule_sets['rules'][$key]['from']."- ".$pricing_rule_sets['rules'][$key]['to']."</td>";

        } else {

          $tempstring1 .= '<td '.$class.'>'.$pricing_rule_sets['rules'][$key]['from']."+</td>";

        }

          $finalprice = $_regular_price;

          $finalprice = number_format(($finalprice - ($finalprice/100)*$pricing_rule_sets['rules'][$key]['amount']), 2);

          $tempstring2.= '<td '.$class.'><span class="amount">'.get_woocommerce_currency_symbol().''.$finalprice."</span></td>";

           $counter++;
        }

       

      }

      $tempstring1 .= '</tr>';

      $tempstring2 .= '</tr>';

      $tempstring  .= $tempstring1 .$tempstring2;

      $tempstring  .= "</table>";

      $tempstring  .= "</div>";

      return $tempstring;

  }

}

add_action('admin_menu', 'dpt_plugin_settings');

function dpt_plugin_settings() {

    add_menu_page('Discount Tables Settings', 'Discount Tables Settings', 'administrator', 'dpt_settings', 'dpt_display_settings');

}

function dpt_display_settings() {

    $deafult = (get_option('dpt_styles') == 'default' || get_option('dpt_styles') == '') ? 'selected' : '';
    $theme2 = (get_option('dpt_styles') == 'theme2') ? 'selected' : '';
    $theme3 = (get_option('dpt_styles') == 'theme3') ? 'selected' : '';
    $theme4 = (get_option('dpt_styles') == 'theme4') ? 'selected' : '';

    $discount_text = get_option('discount_text');

    $show_bulk_savings = get_option('show_bulk_savings');
    
    $show_bulk_savings_true='';
    $show_bulk_savings_false='';

    if($show_bulk_savings=='yes'){
      $show_bulk_savings_true= " checked";
    }else {
      $show_bulk_savings_false= " checked";
    }

    $show_bulk_savings_icon = get_option('show_bulk_savings_icon');

    $show_bulk_savings_icon_true='';
    $show_bulk_savings_icon_false='';

    if($show_bulk_savings_icon=='yes'){
      $show_bulk_savings_icon_true= " checked";
    }else {
      $show_bulk_savings_icon_false= " checked";
    }

    $cheapest_price = get_option('cheapest_price');

    $cheapest_price_true='';
    $cheapest_price_false='';

    if($cheapest_price=='yes'){
      $cheapest_price_true= " checked";
    }else {
      $cheapest_price_false= " checked";
    }

    
 
    $html ='
    <div class="wrap"><form action="options.php" method="post" name="options">
    <h2>Select Your Settings</h2>
    ' . wp_nonce_field('update-options') . '
    <table class="form-table" width="100%" cellpadding="10">
    <tbody>
    <tr>
    <td scope="row" align="left">
     <label>Pricing Table Theme </label>
    <select name="dpt_styles"><option value="default" '.$default .'>Default</option><option value="theme2" '.$theme2 .'>Theme 2</option><option value="theme3" '.$theme3 .'>Theme 3</option><option value="theme4" '.$theme4 .'>Theme 4</option></select></td>
    </tr>
    <tr>
    <td scope="row" align="left">
    <label> Text to display above discount table </label>
    <br/>
    <input type="text" name="discount_text" value="'.$discount_text.'"/>
    </tr>
    <tr>
    <td scope="row" align="left">
    <label> Do you want to display bulk savings on the cart page? </label>
    <br/>
    <input type="radio" name="show_bulk_savings" '.$show_bulk_savings_true.' value="yes">Yes
    <br/>
    <input type="radio" name="show_bulk_savings" '.$show_bulk_savings_false.' value="no">No
    </tr>
    <tr>
    <td scope="row" align="left">
    <label> Do you want to display bulk savings icon on the cart page? </label>
    <br/>
    <input type="radio" name="show_bulk_savings_icon" '.$show_bulk_savings_icon_true.' value="yes">Yes
    <br/>
    <input type="radio" name="show_bulk_savings_icon" '.$show_bulk_savings_icon_false.' value="no">No
    </tr>
    <tr>
    <td scope="row" align="left">
    <label> Do you want to show cheapest discounted price instead of original price on the catalogue? </label>
    <br/>
    <input type="radio" name="cheapest_price" '.$cheapest_price_true.' value="yes">Yes
    <br/>
    <input type="radio" name="cheapest_price" '.$cheapest_price_false.' value="no">No
    </tr>
    </tbody>
    </table>
    
    <input type="hidden" name="action" value="update" />

    <input type="hidden" name="page_options" value="dpt_styles" />
    <input type="hidden" name="page_options" value="discount_text" />
    
     <input type="submit" name="Submit" value="Update" /></form></div>
    
    ';

    echo $html;

}



add_filter('woocommerce_sale_flash', 'dpt_sales_flash');
function dpt_sales_flash($html)
{
  
    global $woocommerce, $post, $product;

     $array_rule_sets = get_post_meta($post->ID, '_pricing_rules', true);
    

     if ($array_rule_sets && is_array($array_rule_sets) && sizeof($array_rule_sets) > 0){
         return "<span class=\"onsale\">Bulk Save!</span>";
     } else {

        return $html;
     }

} // Sales banner filter ends


add_action('woocommerce_before_checkout_form','handling_action');
add_action('woocommerce_after_cart_totals','handling_action');
function handling_action(){
  global $woocommerce;

  $array_rule_sets = get_post_meta($post->ID, '_pricing_rules', true);
  $_regular_price = get_post_meta($post->ID, '_regular_price', true);
  $finalprice=$_regular_price;

  $finalprice = number_format(($finalprice - ($finalprice/100)*$pricing_rule_sets['rules'][$key]['amount']), 2);
  $tempstring2 .= '<span class="amount">'.get_woocommerce_currency_symbol().''.$finalprice."</span></td>";
  $savings_total=0;
  foreach ($woocommerce->cart->cart_contents as $cart_key => $cart_item_array) {
    $savings_total=$savings_total+ $cart_item_array['quantity']*($cart_item_array['discounts']['display_price']- $cart_item_array['discounts']['price_adjusted']);
    $product_id=$cart_item_array[data]->id;
    $meta = get_post_meta( $product_id);
  }
  if($savings_total>0){
    echo '
    <h2>You have saved</h2>
    <table>
    <tbody>
    <tr class="discount">
    <th>Bulk savings</th>
      <td>
        <strong><span class="amount">- '.get_woocommerce_currency_symbol().$savings_total.'</span></strong>
      </td>
    </tr>
    </tbody>
    </table>';
  }

}
//add_filter('woocommerce_get_price_html', 'dpt_woocommerce_price_html');



// epic fail
//add_filter('woocommerce_cart_item_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_empty_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_free_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_free_sale_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_get_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_grouped_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_sale_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_variable_empty_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_variable_free_price_html', 'dpt_woocommerce_price_html');


// epic fail
//add_filter('woocommerce_price_html', 'dpt_woocommerce_price_html');



function dpt_woocommerce_price_html($html){
  $price_clean = strip_tags(preg_replace("/<del>.+?<\/del>/i", "", $html));
   return "From ".$price_clean;
}


}// Check ends for if woocommerce is active and also if the dynamic pricing is active!

?>