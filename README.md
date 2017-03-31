# Wampum Forms
Create login, password, register, subscribe, and free membership (w/ user registration) forms that use the WP-API form processing
* Use a simple shortcode (or PHP function) to create forms throughout your website
* Elegant error/success notices
* Membership form requires WooCommerce and WooCommerce Memberships
* ActiveCampaign integration
* Works well with [Wampum Popups](https://github.com/JiveDig/wampum-popups) plugin
* Easy plugin updates in the WordPress Dashboard via [GitHub Updater plugin](https://github.com/afragen/github-updater)

![Wampum Forms example](assets/wampum-user-forms.jpg)

##Basic Usage##

###Login Form###

Allows a logged out user to login.

####Shortcode####

**Default:**
```
[wampum_login_form]
```

**Custom:**
```
[wampum_login_form title="Login Now!" redirect="https://bizbudding.com"]
```

####PHP####

**Default:**
```
echo wampum_get_login_form();
```

**Custom:**
```
$args = array(
	'title'		=> 'Login Now',
	'redirect'	=> 'https://bizbudding.com/',
);
echo wampum_get_login_form( $args );
```

###Password Form###

Allows a logged in user to change their password.

####Shortcode####

**Default:**
```
[wampum_password_form]
```

**Custom:**
```
[wampum_password_form title="Set A New Password"]
```

####PHP###

**Default:**
```
echo wampum_get_password_form();
```

**Custom:**
```
$args = array(
	'title'		=> 'Change Your Password',
	'redirect'	=> 'https://bizbudding.com/',
);
echo wampum_get_password_form( $args );
```

###Membership Form###

Creates a clean and efficient onboarding flow for adding users to a WooCommerce membership.

####Shortcode####

```
[wampum_membership_form plan_id="26180" title="Join Now!" redirect="https://bizbudding.com/my-account/"]
```

**Shortcode with many options**

```
[wampum_membership_form plan_id="26180" title="Join Now!" title_wrap="h2" desc="Fill out this form to get instant access." first_name=true last_name=false username=false member_message="Woot! You are already a member!" button="Join Now" notifications="mike@bizbudding.com, david@bizbudding.com" redirect="https://bizbudding.com/my-account/"]
```

####PHP####

```
$args = array(
	'plan_id'	=> '1234', // required
	'title'		=> 'Join This Membership!',
	'redirect'	=> 'https://bizbudding.com/',
);
echo wampum_get_membership_form( $args );
```

**PHP function with lots options**

```
$args = array(
	'plan_id'			=> '1234', // required
	'title'				=> 'Join This Membership!',
	'title_wrap'		=> 'h3',
	'desc'				=> 'Fill out this form to get instant access.',
	'redirect'			=> 'https://bizbudding.com/',
	'button'			=> __( 'Join Now!', 'wampum' ),
	'first_name'		=> true,
	'last_name'			=> true,
	'username'			=> true,
	'member_message'	=> 'Woot! You are already a member!',
	'notifications'		=> 'mike@email.com, dave@email.com',
);
echo wampum_get_membership_form( $args );
```

####Logged out users###

* If user tries to login with an existing username/email, they are asked to login first.
If they click the login link it displays the login form.
After successful login, the membership form is loaded and username/email is prefilled.
* After successful submission a user account is created and (if password fields were not used) the password form is loaded.
The user must change their password (password was auto-generated) then they are redirected.
* If SharpSpring parameters are used, the submission is sent to SS during processing.

####Logged in users####

* User fields are pre-filled, and username/email fields are readonly
* After submission, user is redirected

##Shortcode parameters & PHP args##

Most of these parameters work with all form types. Form specific parameters listed below.

####hidden####

(boolean) true|false

**Default** `false`

Whether to hide (adds display:none;) the form

Mostly used internally for membership form

---

####inline####

(boolean) true|false

**Default** `false`

Whether to display the form fields as inline columns

[_requires Flexington_](https://github.com/JiveDig/flexington)

---

####title####

(string) 'My Form Title'

**Default** `{different per form]`

Change the heading of the form

---

####title_wrap####

(string) 'h4'

**Default** `'h3'`

Change title wrapping element

---

####desc####

(string) 'Fill out the form below'

**Default** `null`

Add a description below the form title

---

####first_name####

(boolean) true|false

**Default** `false`

Whether to show the first name field

---

####first_name_label####

(string) 'Name' or 'First Name {if last name fields is used}'

**Default** `'Name' or 'First Name {if last name field is used}'`

First name field label

---

####last_name####

(boolean) true|false

**Default** `false`

Whether to show the last name field

---

####last_name_label####

(string) 'Last Name'

**Default** `'Last Name'`

Last name field label

---

####email####

(boolean) true|false

**Default** `false`

Whether to show the email field

---

####email_label####

(string) 'Email'

**Default** `'Email'`

Email field label

---

####username####

(boolean) true|false

**Default** `false`

Whether to show the username field

---

####username_label####

(string) 'Username'

**Default** `'Username' or 'Username/Email' on login form`

Username field label

---

####password####

(boolean) true|false

**Default** `false`

Whether to show the password field

---

####password_label####

(string) 'Password'

**Default** `'Password'`

Password field label

---

####password_confirm####

(boolean) true|false

**Default** `false`

Whether to show the confirm password field

---

####password_confirm_label####

(string) 'Password'

**Default** `'Password'`

Password field label

---

####password_strength####

(boolean) true|false

**Default** `false`

Whether to show the password strength field

---

####password_strength_label####

(string) 'Password'

**Default** `'Password'`

Password strength field label

---

####notifications####

(string) 'mike@email.com,david@email.com'

**Default** `{none}`

Where to send email notifications after successful submission

---

####redirect####

(string) 'https://bizbudding.com'

**Default** `{current page URL}`

Where to redirect after successful submission

---

####ac_list_ids####

(string) '1,3,12'

**Default** `{none}`

ActiveCampaign list IDs to add user to

_Requires valid credentials in Settings > Wampum Forms_

---

####ac_tags####

(string) 'some tag,leads'

**Default** `{none}`

ActiveCampaign tags to add to user

_Requires valid credentials in Settings > Wampum Forms_

---

####button####

(string) 'Submit'

**Default** `'Log In'`

Submit button text

---

###Login Form###

####remember####

(boolean) true|false

**Default** `true`

Show the remember me checkbox

---

####value_remember####

(boolean) true|false

**Default** `true`

Default "Remember Me" checked or unchecked

---

###Register Form###

####log_in####

(boolean) true|false

**Default** `false`

Whether to auto log in the user after successful registration

###Membership Form###

####plan_id (**required**)####

(integer) 1234

**Default** `null`

Membership ID that this form will add the user to

---

####member_message####

(string) true|false

**Default** `null`

Display a message in place of the form if a logged in user is already a member

---
