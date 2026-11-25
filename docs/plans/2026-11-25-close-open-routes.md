# Close the open Wampum Forms routes

Agreed with Mike on November 25, 2026. Patch release 1.5.1. No new settings.

## Why

The REST routes in `includes/class-submissions.php` trust whatever the browser sends. Every route has `'permission_callback' => '__return_true'`, which is fine for public forms, but the values that should come from the site come from the request instead.

- **Signup with registration off.** `POST /wampum/v1/register/` makes an account on any site running the plugin, even when Settings > General > "Anyone can register" is off. All five sites still using Wampum have it off (checked November 25, 2026).
- **Any membership plan.** `POST /wampum/v1/membership-add/` takes `plan_id` from the request and calls `wc_memberships_create_user_membership()`. Anyone can grant themselves any plan, including paid ones. thepaleomom.com sells about 15 plans this way and qwikcoach.com several.
- **Any ActiveCampaign list or tag.** `ac_list_ids` and `ac_tags` come from the request on the subscribe, register and membership routes.
- **Any notification address.** `notifications` comes from a hidden field, so a request can make the site email anyone.

Reported for the User Locations plugin by an outside researcher, whose proof used the register route. User Locations is archived and runs nowhere.

## The fix

### 1. Sign the values the site sets

In `includes/class-forms.php`, every form that outputs any of `plan_id`, `ac_list_ids`, `ac_tags`, `notifications` or `redirect` also outputs a hidden `wampum_sig` field.

- The signature is `wp_hash( wp_json_encode( [ $form_type, $plan_id, $ac_list_ids, $ac_tags, $notifications ] ) )`, with empty strings for values the form does not have. JSON rather than joining on `|`, so a `|` inside one value cannot shift text into the next. Put this in one helper used by both the form and the route, so the two can never build it differently.
- No nonce. Pages with these forms are cached, and `wp_hash()` stays stable until the salts change.
- `js/wampum-forms.js` sends `wampum_sig` with every request that sends those fields, including the membership flow's two steps. Rebuild `wampum-forms.min.js`.

In `includes/class-submissions.php`, every route that reads those fields recomputes the signature from the request and refuses with `hash_equals()` false. The error reads: "This form is out of date. Reload the page and try again."

Check `redirect` the same way only if it is used for anything other than a same-site redirect. If it already goes through `wp_safe_redirect()` rules, leave it.

### 2. Shut the bare signup route

Split `register()` into the route callback and the internal worker. The route callback refuses unless `get_option( 'users_can_register' )` is on, or the filter `wampum_forms_allow_register` returns true. `membership_add()` keeps calling the worker directly, because its signed `plan_id` already shows the site offered that signup.

### 3. Leave everything else alone

Login and password routes keep working as before, with one addition found while building: both read `notifications` (and login also the ActiveCampaign fields), so they check the signature too. A request that sends none of the signed values is not checked, so a plain login from a page cached before the update still works. No settings screen, no new options.

## Verify

Write PHPUnit coverage if the repo gains a harness. It has none today, so at minimum:

- On a local site with WooCommerce Memberships, the free membership form on a page still works end to end.
- The same request with `plan_id` changed is refused, and no membership is made.
- `POST /wampum/v1/register/` with registration off is refused, and no user is made.
- A subscribe form still adds the contact, and changing `ac_list_ids` or `notifications` is refused.
- An already-open page from before the update (old markup, no `wampum_sig`) shows the reload message rather than failing silently.

## Rollout

Sites that need 1.5.1, from the November 25, 2026 sweep:

- qwikcoach.com and dev.qwikcoach.com: membership and subscribe forms.
- thepaleomom.com: membership form on `free-membership`.
- tesswhaley.com: one subscribe form, in a Mai Section on `face-body-naturally`. It runs 1.1.4 from a folder named `wampum-forms-master`. Check the updater finds it before relying on an update.

Being turned off instead: bizbudding.com and maitheme.com now, and mnrovers.org once its login box moves to mai-auth.

Tagging needs Mike's yes, per release.

## Abuse check, November 25, 2026

Wampum leaves a note on each membership it creates: "Membership added via Wampum form at <url>".

- qwikcoach.com: 231 memberships across 9 plans, the last in 2021 except one on November 23, 2025 from `qwikcoach-registration` for the free plan. Nothing looks forged.
- thepaleomom.com: 17,115 for Members Area (the free plan the page offers) and 338 for Paleo Principles Pre-Order in 2017. Two for **Guide to Nutrivore**, a paid plan, on September 7 and October 3, 2022, both from `free-membership`. No post, Mai Section or meta anywhere names plan 203473 in a Wampum form, and that page keeps no revisions, so whether a 2022 version offered it can't be proven. Worth a look by whoever runs the shop.
