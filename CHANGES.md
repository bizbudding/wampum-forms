# Changelog

## 1.4.0 (1/25/21)
* Fixed: Added permission_callback to all registered REST API routes.
* Changed: Updated dependencies.

## 1.3.3 (4/2/18)
* Fixed: Undefined variable when sending notifications causing form to hang in some scenarios.
* Fixed: Properly chain ajax fail for debugging.

## 1.3.2 (3/30/18)
* Added: Force equal height fields when inline is true.
* Fixed: Undefined variable in register form.
* Fixed: first_name_label default if last_name field is showing.

## 1.3.1 (1/31/18)
* Fixed: Password strength script not being loaded on registration forms.

## 1.3.0 (11/16/17)
* Added: Event Organiser Pro integration to send Active Campaign data via Booking forms. Set list IDs via 'AC List IDs' and tags via 'AC Tags' taxonomy metaboxes.

## 1.2.4 (10/31/17)
* Fixed: Allow #hash in form redirect.

## 1.2.3 (10/12/17)
* Fixed: Align labels to the left, so Mai Pro sections with centered content won't center labels.

## 1.2.2 (10/12/17)
* Added: Included updater script.

## 1.2.1 (10/12/17)
* Fixed: Scripts were not loading when using form in a Gravity Forms confirmation.

## 1.2.0
* Changed: Form titles no longer show by default. You have to specify the title="Some Title" in the shortcode parameters now.

## 1.1.4
* Fixed: wp_set_current_user() after wp_signon() to ensure use is logged in fully

## 1.1.3
* Changed: Allow password_strength parameter in membership form

## 1.1.2
* Fixed: empty first name label when logged in and viewing [wampum_membership_form]

## 1.1.1
* Fixed: unexpected capitalization of Plan_id parameter on Instructions section of the settings page

## 1.1.0
* Added: Active Campaign support via new settings page
* Changed: Full rebuild with new Wampum_Form() class to build forms
* Changed: Drop Sharpspring Support until reintegration

## 1.0.0
* Launch
