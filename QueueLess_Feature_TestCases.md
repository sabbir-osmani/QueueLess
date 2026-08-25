# QueueLess — Full Feature List & Test Case Guide

This document lists every feature in the QueueLess project (A–Z by area) and gives you a step-by-step test case for each one, so you can confirm the simplified project behaves exactly like the original.

Format for each test case:

- **Steps** — what to click/do
- **Expected Result** — what should happen if it's working correctly

---

## A. Public / Guest Access (not logged in)

### A1. View home page

**Steps:** Go to `index.php` (site root) without logging in.
**Expected:** Hero section, 4 service cards (Accounts, Library, CSE, Lab), "How It Works" steps, and Login/Register buttons in navbar.

### A2. Guest cannot access student pages

**Steps:** While logged out, manually visit `dashboard.php`, `queue.php`, `history.php`, `cancelToken.php`.
**Expected:** Every one of them redirects you to `login.php`.

### A3. Guest cannot access admin pages

**Steps:** While logged out, manually visit `admin/dashboard.php`, `admin/completeToken.php`, `admin/nextToken.php`, `admin/skipToken.php`, `admin/resetQueue.php`.
**Expected:** Every one of them redirects you to `admin/login.php`.

---

## B. Student Registration

### B1. Register a new account

**Steps:** Go to `register.php`. Fill in Name, Email, Password (6+ chars), Confirm Password matching. Submit.
**Expected:** Account created, you're automatically logged in, redirected to `dashboard.php`, welcome message shows your name.

### B2. Password too short

**Steps:** Register with a password under 6 characters.
**Expected:** Error message "Password must be at least 6 characters", account NOT created.

### B3. Passwords don't match

**Steps:** Enter different values in Password and Confirm Password.
**Expected:** Error message "Passwords do not match".

### B4. Duplicate email

**Steps:** Register using an email that's already registered.
**Expected:** Error message "This email is already registered", no duplicate account created.

### B5. Password is stored safely

**Steps:** N/A (verify in database) — check the `students` table `pass` column after registering.
**Expected:** Password is a long bcrypt hash, never the plain password.

---

## C. Student Login / Logout

### C1. Successful login

**Steps:** Go to `login.php`, enter correct email + password.
**Expected:** Redirected to `dashboard.php`, logged in.

### C2. Wrong password

**Steps:** Enter a registered email with the wrong password.
**Expected:** Error message "Wrong password", stays on login page.

### C3. Unregistered email

**Steps:** Enter an email that was never registered.
**Expected:** Error message "No account found with this email".

### C4. Logout

**Steps:** While logged in, click "Logout" in navbar.
**Expected:** Session ends, redirected to `login.php`. Trying to revisit `dashboard.php` now redirects to login again.

---

## D. Student Dashboard

### D1. No active token view

**Steps:** Log in as a student with no current token, view `dashboard.php`.
**Expected:** "No active token" message, and all 4 service cards show a "Take Token" button.

### D2. Taking a token from the dashboard

**Steps:** Click "Take Token" on any service card (e.g. Library).
**Expected:** Redirects to `queue.php?service=library`, a new token is created and shown.

### D3. Active token displayed correctly

**Steps:** With an active token (e.g. L005, waiting), reload `dashboard.php`.
**Expected:** "Your Active Token" card shows "L005 (Library)" and "Status: Waiting".

### D4. Other services locked while you have an active token

**Steps:** While holding an active token for Library, look at the Accounts/CSE/Lab cards.
**Expected:** Those 3 cards show a disabled "Locked" button — you cannot take a token for another service until your current one is finished/cancelled.

### D5. Your own service card is different

**Steps:** While holding an active Library token, look at the Library card specifically.
**Expected:** It shows "Track Queue" button, plus either "Cancel Token" (if status = waiting) or a disabled "Being Served" label (if status = serving) — NOT locked.

### D6. "Last used" badge

**Steps:** Take a token for a service, let it complete/cancel, come back to the dashboard later.
**Expected:** That service's card shows a small "Last used" badge (remembered via cookie for 30 days).

### D7. Live auto-refresh on dashboard

**Steps:** While an active token is showing on the dashboard, have an admin call/serve/skip/complete it from another browser/tab, then wait up to 5 seconds without reloading.
**Expected:** The status card updates automatically (new status text, button changes) without a manual page refresh.

### D8. Token cleared automatically after being completed/cancelled

**Steps:** While your dashboard is open and polling, have your token marked completed by an admin (or cancel it yourself elsewhere).
**Expected:** Within 5 seconds the dashboard page reloads itself and shows "No active token" again.

---

## E. Taking a Token / Queue Page (`queue.php`)

### E1. Take a brand-new token

**Steps:** As a student with no active token, visit `queue.php?service=cse`.
**Expected:** A new token like "C003" is created and shown as "Your Token", status "Waiting".

### E2. Token numbering is sequential per service

**Steps:** Take several tokens for the same service over time (as different students).
**Expected:** Numbers increase in order per service: C001, C002, C003... Each service has its own independent counter (A001 for Accounts doesn't affect L001 for Library).

### E3. Revisiting the queue page shows the same token

**Steps:** After taking a token, revisit `queue.php?service=cse` again (same service) without cancelling.
**Expected:** Shows your existing token — does NOT create a second new token.

### E4. Cannot take a token for a 2nd service

**Steps:** While holding an active token for Library, manually visit `queue.php?service=lab`.
**Expected:** You're redirected back to `queue.php?service=library` — your existing active service — not allowed to start a second one.

### E5. Invalid service name blocked

**Steps:** Manually visit `queue.php?service=somethingfake`.
**Expected:** Redirected to `dashboard.php` (only accounts/library/cse/lab are valid).

### E6. "Now Serving" and "People Ahead" are accurate

**Steps:** Take a token, then check "Now Serving" and "People Ahead of You" numbers against the actual waiting list.
**Expected:** "People Ahead" = number of tokens still waiting that were created before yours. "Now Serving" shows the current in-progress token for that service, or "None yet".

### E7. Estimated wait time

**Steps:** Check the "Estimated wait" text under People Ahead.
**Expected:** Shows `peopleAhead × 5` minutes.

### E8. Live auto-refresh on queue page

**Steps:** Stay on `queue.php`, have an admin call the next token (moving the queue forward), wait up to 5 seconds.
**Expected:** "Now Serving," "People Ahead," and your own status update automatically without reloading.

### E9. lastService cookie is set

**Steps:** Take a token for any service, then check dashboard later.
**Expected:** That service shows the "Last used" badge (cookie persists 30 days).

---

## F. Cancelling a Token

### F1. Cancel while waiting

**Steps:** With a token in "waiting" status, click "Cancel Token" (on dashboard or queue page) and confirm the popup.
**Expected:** Token status becomes "cancelled". Dashboard now shows "No active token." You're free to take a new token for any service.

### F2. Cannot cancel a token that's already being served

**Steps:** Once your token status is "serving" (admin called you), check the button.
**Expected:** Instead of a Cancel button, you see a disabled "Being Served" label — cancelling is not possible at that stage.

### F3. Cannot cancel someone else's token

**Steps:** Manually change the URL to `cancelToken.php?id=<someone else's token id>`.
**Expected:** Nothing happens to that token — the code only cancels tokens that belong to your own logged-in student ID.

### F4. Confirmation prompt appears

**Steps:** Click "Cancel Token".
**Expected:** A browser confirm popup ("Cancel this token?") appears before anything happens; clicking Cancel/No leaves the token untouched.

---

## G. Student History Page

### G1. View history

**Steps:** Log in, click "History" in navbar.
**Expected:** Table of every token you've ever taken, newest first, with Token #, Service name, Date, and colored Status pill (Completed/Skipped/Cancelled/Waiting).

### G2. Empty history

**Steps:** View history as a brand-new student who hasn't taken any tokens.
**Expected:** "You have no token history yet." message shown instead of an empty table.

### G3. Status colors match

**Steps:** Compare status pill colors.
**Expected:** Completed = green, Skipped = red, Cancelled = gray, Waiting/other = orange.

---

## H–P. Admin Features

### H1. Admin login

**Steps:** Go to `admin/login.php`, enter valid admin email + password.
**Expected:** Redirected to `admin/dashboard.php`, showing only the ONE service that admin is assigned to.

### H2. Wrong admin password / unknown email

**Steps:** Try incorrect credentials.
**Expected:** "Wrong password" or "No admin account found with this email" errors, same pattern as student login.

### I1. Admin sees only their assigned service

**Steps:** Log in as an admin assigned to "library".
**Expected:** Dashboard title and tab both say "Library" — no way to switch to another service's queue from this account.

### I2. Admin with no assigned service

**Steps:** (Requires a DB admin row with `service` = NULL or invalid) Log in as that admin.
**Expected:** Page stops with the message: "Your admin account has no service assigned. Please contact the system administrator to fix this in the admins table." — this is a safety check, not a crash.

### J1. Call Next (no one currently serving)

**Steps:** As admin, with "Now Serving" empty and at least one student waiting, click "Call Next".
**Expected:** The oldest waiting token becomes "serving" and appears in the "Now Serving" card with the student's name.

### J2. Call Next with an empty queue

**Steps:** Click "Call Next" when no one is waiting.
**Expected:** Nothing happens — "Now Serving" stays empty, no error.

### J3. Cannot call next while already serving someone

**Steps:** With someone already in "serving" status, try to trigger `nextToken.php` directly.
**Expected:** No change — a new token can't start serving until the current one is completed or skipped.

### K1. Mark Completed

**Steps:** With a token in "serving" status, click "Mark Completed".
**Expected:** That token's status becomes "completed", "Served Today" count goes up by 1, "Now Serving" becomes empty again, and the button changes back to "Call Next".

### L1. Skip

**Steps:** With a token in "serving" status, click "Skip".
**Expected:** Token status becomes "skipped", "Skipped" count goes up by 1, "Now Serving" clears, button reverts to "Call Next".

### M1. Reset Queue

**Steps:** With several students waiting and/or one being served, click "Reset Queue".
**Expected:** ALL waiting and serving tokens for this service become "completed" in one action. Waiting list becomes empty, waiting count becomes 0.

### N1. Waiting list table accuracy

**Steps:** Have 3 students take tokens for the same service. View the admin dashboard.
**Expected:** All 3 appear in the waiting list table in the order they took their token (oldest first), with correct Token #, Student Name, and Time Taken.

### N2. Waiting/Served/Skipped counters

**Steps:** Perform a mix of actions (some completed, some skipped, some still waiting) throughout a day.
**Expected:** "Waiting" = current count still in queue. "Served Today" and "Skipped" only count actions from today (`DATE(created_at) = CURDATE()`), not historical days.

### O1. Live auto-refresh on admin dashboard

**Steps:** Keep the admin dashboard open. From another window, have a student take a new token for that service. Wait up to 5 seconds.
**Expected:** New student appears in the waiting list and the "Waiting" counter increases automatically, no manual refresh needed.

### P1. Admin logout

**Steps:** Click "Logout" on the admin dashboard.
**Expected:** Session ends, redirected to `admin/login.php`.

---

## Q–S. Cross-Cutting / Security Behaviors

### Q1. One active token per student, enforced everywhere

**Steps:** Try every route to get a 2nd active token (dashboard buttons, direct URL to `queue.php?service=X`).
**Expected:** All routes are blocked/redirected the same way — a student can never hold 2 active tokens across different services at once.

### Q2. Session-based access control

**Steps:** Log in as a student in one browser tab, then open `admin/dashboard.php` in the same browser.
**Expected:** Still redirected to admin login — student session and admin session are separate; being logged in as one does not grant access to the other.

### R1. Special characters in names don't break the page

**Steps:** Register a student with a name containing characters like `<b>Test</b>` or `O'Brien & Co`.
**Expected:** The name displays literally as typed (e.g., showing the actual `<b>` text) everywhere it appears — dashboard welcome message, admin waiting list, history — rather than being interpreted as HTML or breaking the layout.

### S1. SQL-safe inputs

**Steps:** Try entering something like `' OR '1'='1` into the login email or password field.
**Expected:** Login simply fails normally ("No account found" / "Wrong password") — it does not log you in or cause a database error, because all queries use prepared statements.

---

## T. Responsive / Visual Behavior

### T1. Mobile layout

**Steps:** Resize browser (or use phone) to under 600px width, view home page, dashboard, admin dashboard.
**Expected:** Service grids stack to 1 column, navbar stacks vertically, buttons go full-width — matches original responsive design exactly (untouched CSS breakpoints).

### T2. Tablet layout

**Steps:** Resize to ~700–900px width.
**Expected:** Service grids show 2 columns (dashboard/home) as before.

---

## U–Z. Miscellaneous

### U1. Favicon and logos load

**Steps:** Check browser tab icon and navbar logo images on any page.
**Expected:** Icons display correctly (requires you to have copied your original `images/` files into the new project — see `images/README_IMAGES.txt`).

### V1. All 4 services work identically

**Steps:** Repeat the full "take token → track → complete" flow once for each of: Accounts, Library, CSE, Lab.
**Expected:** Identical behavior for all 4 — only names, prefixes (A/L/C/B), and admin assignment differ.

### W1. Direct URL access still respects login state

**Steps:** Log out, then try pasting a direct link to a queue page you previously had open.
**Expected:** Redirected to login — no page is accessible just by knowing the URL.

---

## Suggested Test Order (fastest way to verify everything at once)

1. Register 2 test students, log in as each in 2 separate browsers (or normal + incognito window).
2. Create 4 admin accounts (one per service) — or reuse existing ones — log into each in separate tabs.
3. Have both students take tokens for the _same_ service (e.g. Library) → confirm sequential numbering and "People Ahead" count.
4. From the Library admin tab: Call Next → confirm first student sees "serving" status live within 5 seconds.
5. Mark Completed → confirm "Served Today" count increases and student's dashboard reloads to "No active token."
6. Have the 2nd student cancel their own waiting token → confirm it disappears from the admin waiting list live.
7. Check both students' History pages show correct status entries.
8. Try 2-3 of the "should be blocked" test cases (Q1, E4, E5, A2, A3) to confirm nothing was accidentally opened up during simplification.

If all of the above pass, the simplified project matches the original feature-for-feature.
