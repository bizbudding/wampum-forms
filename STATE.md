# State
Updated: 2026-11-25 by Claude (Opus 5.5)

## Now

1.5.1 is released (tag pushed, master at develop) and pushed by mai-sites to all four sites on 2026-11-25. tesswhaley.com now runs it from `wampum-forms`; the old `wampum-forms-master` folder was deleted. It signs the values a form sets (plan_id, ac_list_ids, ac_tags, notifications) in a hidden `wampum_sig` field, and refuses requests that change them. `POST /wampum/v1/register/` refuses unless "Anyone can register" is on or `wampum_forms_allow_register` returns true. Plan: `docs/plans/2026-11-25-close-open-routes.md`.

## Next

- Retire Wampum Forms on every site (TODO.md), then archive the GitHub repo. It is public and holds the plan, so archive rather than fix history.
- tesswhaley.com's domain redirects to a domain-for-sale page, so its form is not reachable anyway.

## Blocked / waiting on

Nothing.

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
- Known limit: anyone who can preview a post (Contributor and up) can render a form with any plan_id and get a valid signature. Left as is for 1.5.1 on 2026-11-25. Before 1.5.1 anyone could do it with no account.
