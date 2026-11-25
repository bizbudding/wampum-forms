# State
Updated: 2026-11-25 by Claude (Opus 5.5)

## Now

1.5.1 is built and committed on `develop`, not pushed or tagged. It signs the values a form sets (plan_id, ac_list_ids, ac_tags, notifications) in a hidden `wampum_sig` field, and refuses requests that change them. `POST /wampum/v1/register/` refuses unless "Anyone can register" is on or `wampum_forms_allow_register` returns true. Plan: `docs/plans/2026-11-25-close-open-routes.md`.

## Next

- Mike's yes to push `develop`, merge to `master` and tag 1.5.1.
- Roll out to qwikcoach.com, dev.qwikcoach.com, thepaleomom.com and tesswhaley.com (see the plan's Rollout section). tesswhaley.com runs 1.1.4 from a folder named `wampum-forms-master`, so check the updater finds it.
- Then retire Wampum Forms (TODO.md).

## Blocked / waiting on

Mike: "Push and tag 1.5.1?"

## Verify

Verified 2026-11-25 on qwikcoach.test (plugin symlinked to this repo), with ActiveCampaign pointed at a local mock through a temporary mu-plugin, since the real one would reach the client's account:

- Free membership form on `/qwikcoach-registration/`, headless Chromium: user made, plan 4603 active, AC contact sync sent.
- membership-add with plan_id changed to 3710, or sent as an array: refused, no user, no membership.
- register with registration off: "Registration is closed on this site." With the filter on: user made. With the filter on and an unsigned ac_list_ids: refused.
- Subscribe form in the browser: success and AC sync. Changed ac_list_ids or notifications by curl: refused, no AC call, no mail.
- Old markup (wampum_sig removed from the page): the form shows "This form is out of date. Reload the page and try again."
- Login with an unsigned notifications value: refused. Plain login: normal WordPress error.

## Gotchas

- The signature helper is `wampum_forms_get_signature()` in `includes/helpers.php`. Forms and routes must both use it.
- A request with none of the signed values is not checked, so cached plain login forms keep working. Membership requests always carry plan_id, so they are always checked.
- The site enqueues `js/wampum-forms.js`, not the `.min.js`. Rebuild the min file anyway: `npx terser js/wampum-forms.js --compress --mangle -o js/wampum-forms.min.js`.
- No PHPUnit harness. Verification is by hand on a local site.
- Floor is PHP 8.0 and WP 6.0. Do not use 8.1+ syntax.
- membership_add() runs notifications and ActiveCampaign twice (once in create_user(), once itself). Pre-existing, left alone.
