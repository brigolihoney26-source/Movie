# AbsoluteSineMatik

A movie and series streaming-style website built with **PHP + MySQL** for **XAMPP**.

## How to run (XAMPP + Visual Studio Code)

1. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Copy the whole `AbsoluteSineMatik` folder into `C:\xampp\htdocs\`.
3. Open **http://localhost/phpmyadmin** → **Import** tab → choose
   `database/absolutesinematik.sql` → **Go**.
   (Don't select a database first; the file creates `absolutesinematik` by itself.)
4. Open **http://localhost/AbsoluteSineMatik/** in your browser.
5. To edit the code: in Visual Studio Code use **File → Open Folder** and pick the folder inside `htdocs`.

Default admin account: `admin@absolutesinematik.com` / `Admin@123`
(the admin sees the complete login/register history).

If your MySQL has a password or a different user, edit `config/database.php`.

## Folder structure

```
AbsoluteSineMatik/
├── index.php          Main page - Movies (no Back button)
├── series.php         Series catalog
├── movie.php          Detail page for one movie/series
├── watch.php          "Watch Now" player page (login required)
├── login.php          Login
├── register.php       Register
├── logout.php         Logout
├── history.php        Login / register history
├── poster.php         Placeholder poster when a title has no picture
├── tools/
│   └── fetch_posters.php  One-click real poster downloader (admin)
├── assets/posters/    Put your own poster pictures here (1.jpg, 2.png...)
├── config/
│   ├── app.php        Site name, page size, timezone
│   └── database.php   Database connection settings
├── includes/
│   ├── bootstrap.php  Loads everything + starts the session
│   ├── functions.php  Helpers (escape, CSRF, history log, formatting)
│   ├── movies.php     All movie/series database queries
│   ├── header.php     Top bar (logo, tabs, search, Login)
│   ├── footer.php     Footer + script
│   ├── catalog.php    Hero + tabs + carousel + grid (shared by index/series)
│   ├── hero.php       Big featured title block
│   ├── card.php       One poster card
│   └── pagination.php Page numbers
├── assets/
│   ├── css/style.css  All styles
│   └── js/main.js     Search, filter menu, carousel arrows, back button
└── database/
    └── absolutesinematik.sql   Tables + 150 titles (110 movies, 40 series)
```

## Posters (real pictures)

**Easiest - add your own pictures:** save an image into `assets/posters/` named with the title's ID
(`1.jpg`, `8.png`...). The list of IDs is in `assets/posters/README.txt`. Your file always wins.
Priority: your file → `poster_url` in the database → auto placeholder.

**Or** let the tool find them automatically:

1. Log in as admin (`admin@absolutesinematik.com` / `Admin@123`).
2. Click **Fetch posters now** in the yellow bar at the bottom of the page
   (or open `http://localhost/AbsoluteSineMatik/tools/fetch_posters.php`).
3. Wait about a minute. The tool finds each title's real poster on Wikipedia and saves the image
   address in the `poster_url` column. You must be online.

Notes:
* If you get an SSL error on XAMPP, the page shows a button to run it without the certificate check.
* To use a different picture, right-click any image in Google Images → **Copy image address**
  and paste it into that title's `poster_url` in phpMyAdmin.
* Titles with no poster (or a broken link) show an automatic placeholder from `poster.php`.

You can also set `trailer_url` (otherwise a YouTube search is used) and `video_url`
(a direct `.mp4` link that plays on the Watch Now page).

## Login / register history

Every login, register and logout is saved in the `login_history` table with the
email, action, success/failed, IP address, date and a **hash** of the password typed.
Passwords are hashed (never stored as plain text), and `history.php` shows them to the admin.
