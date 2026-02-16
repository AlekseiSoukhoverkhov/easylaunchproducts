<?php
/**
 * Template part for the Order Form.
 * Loaded via EasyProducts_Order_Form::easypr_display_order_form()
 *
 * @package EasyProducts
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

// Extract variables
$easypr_product_title    = $args['product_title'] ?? '';
$easypr_submission_error = $args['submission_error'] ?? '';

// Check the GET flag set by the handler upon successful submission
$easypr_is_submitted = isset( $_GET['easypr_submitted'] ) && $_GET['easypr_submitted'] === 'true';

if ( $easypr_is_submitted ) :
    ?>
    <div id="product-form" class="easypr-message easypr-message-success">
        <p><?php echo esc_html__( 'Thank you! Your order was successfully sent.', 'easylaunchproducts' ); ?></p>
    </div>
<?php
endif;

if ( $easypr_submission_error ) :
    ?>
    <div id="product-form" class="easypr-message easypr-message-error">
        <p><strong><?php echo esc_html__( 'Submission Error:', 'easylaunchproducts' ); ?></strong></p>
        <p><?php echo wp_kses_post( $easypr_submission_error ); ?></p>
    </div>
<?php
endif;

// Prepare field values for display in case of submission error
// The nonce check must be performed before accessing $_POST data
$easypr_name     = '';
$easypr_email    = '';
$easypr_phone    = '';
$easypr_quantity = 1;
$easypr_comment  = '';

// Check if there was a form submission (and thus potential data in $_POST)
// and verify the nonce to ensure the data is coming from a legitimate form.
if ( isset( $_POST['easypr_order_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['easypr_order_nonce'] ), 'easypr_order_action' ) ) {
    // Nonce is valid, now safely prepare $_POST data for output.
    // Use wp_unslash and sanitize before outputting.
    $easypr_name     = isset( $_POST['easypr_name'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_name'] ) ) : '';
    $easypr_email    = isset( $_POST['easypr_email'] ) ? sanitize_email( wp_unslash( $_POST['easypr_email'] ) ) : '';
    $easypr_phone    = isset( $_POST['easypr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['easypr_phone'] ) ) : '';
    // Quantity is a number, sanitize it as an integer.
    $easypr_quantity = isset( $_POST['easypr_quantity'] ) ? intval( $_POST['easypr_quantity'] ) : 1;
    // Comment is a textarea, use sanitize_textarea_field.
    $easypr_comment  = isset( $_POST['easypr_comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['easypr_comment'] ) ) : '';
}

?>

<div class="easypr-order-form-wrapper" id="product-form">
    <h3 class="easypr-form-title">
        <?php echo esc_html__( 'Order this Product', 'easylaunchproducts' ); ?>:
        <?php echo esc_html( $easypr_product_title ); ?>
    </h3>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="easypr-form-main">

        <input type="hidden" name="action" value="easypr_order_submit">

        <?php wp_nonce_field( 'easypr_order_action', 'easypr_order_nonce' ); ?>

        <input type="hidden" name="easypr_product_title" value="<?php echo esc_attr( $easypr_product_title ); ?>">

        <div class="easypr-field-row">
            <label for="easypr_name" class="easypr-label"><?php echo esc_html__( 'Name', 'easylaunchproducts' ); ?> *</label>
            <input type="text" id="easypr_name" class="easypr-input easypr-text-input"
                   name="easypr_name"
                   value="<?php echo esc_attr( $easypr_name ); ?>" required>
        </div>

        <div class="easypr-field-row">
            <label for="easypr_email" class="easypr-label"><?php echo esc_html__( 'Email', 'easylaunchproducts' ); ?> *</label>
            <input type="email" id="easypr_email" class="easypr-input easypr-email-input"
                   name="easypr_email"
                   value="<?php echo esc_attr( $easypr_email ); ?>" required>
        </div>

        <div class="easypr-field-row">
            <label for="easypr_phone" class="easypr-label"><?php echo esc_html__( 'Phone', 'easylaunchproducts' ); ?></label>
            <input type="tel" id="easypr_phone" class="easypr-input easypr-tel-input"
                   name="easypr_phone"
                   value="<?php echo esc_attr( $easypr_phone ); ?>">
        </div>

        <div class="easypr-field-row">
            <label for="easypr_quantity" class="easypr-label"><?php echo esc_html__( 'Quantity', 'easylaunchproducts' ); ?> *</label>
            <input type="number" id="easypr_quantity" class="easypr-input easypr-number-input"
                   name="easypr_quantity"
                   value="<?php echo esc_attr( $easypr_quantity ); ?>"
                   min="1" required>
        </div>

        <div class="easypr-field-row">
            <label for="easypr_comment" class="easypr-label"><?php echo esc_html__( 'Comment', 'easylaunchproducts' ); ?></label>
            <textarea id="easypr_comment" class="easypr-input easypr-textarea-input"
                      name="easypr_comment"
                      rows="4"><?php echo esc_textarea( $easypr_comment ); ?></textarea>
        </div>

        <div class="easypr-submit-row">
            <input type="submit" name="easypr_order_submit"
                   class="easypr-submit-button"
                   value="<?php echo esc_attr__( 'Send Order', 'easylaunchproducts' ); ?>">
        </div>

    </form>
</div>