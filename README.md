# AI-Based Mehndi Design Recommendation Website

Mehndi Shop is a beginner-friendly PHP/MySQL website for discovering designs. Its main feature is an **AI-inspired, rule-based recommendation system**: each exact design preference match adds two points and a matching budget adds three points, for a maximum of thirteen match points. Customer ratings add up to two priority points. It does not use a trained AI/ML model or an external AI API.

## 1. Install and start XAMPP

1. Download XAMPP for Windows from [apachefriends.org](https://www.apachefriends.org/).
2. Run the installer. Install **Apache**, **MySQL**, and **phpMyAdmin** (the other bundled tools are not needed for this project).
3. Open **XAMPP Control Panel**. If Windows asks about firewall access, allow Apache on your private network.
4. Click **Start** beside **Apache**, then click **Start** beside **MySQL**. Both rows should show that they are running.
5. Keep the control panel open while using the site. If Apache or MySQL does not start, another program may already be using its port; close that program or resolve the port conflict before continuing.

This workspace is already under `C:\xampp1\htdocs`. The project folder is:

```text
C:\xampp1\htdocs\Mehandi1234\Mehndi_AI
```

If your XAMPP installation uses the usual `C:\xampp\htdocs` folder instead, copy the complete `Mehndi_AI` folder there and use `http://localhost/Mehndi_AI/` as the website address. Do not open PHP files by double-clicking them; Apache must serve them.

## 2. Create the database and tables

1. With Apache and MySQL running, open `http://localhost/phpmyadmin/`.
2. Select **New**, enter `mehndi_db` as the database name, choose the `utf8mb4_unicode_ci` collation if offered, and click **Create**.
3. Select the new `mehndi_db` database in the left sidebar.
4. Choose **Import**, click **Choose File**, select `C:\xampp1\htdocs\Mehandi1234\Mehndi_AI\database.sql`, then click **Import** or **Go** at the bottom.
5. The import creates the `users`, `categories`, `designs`, `favorites`, `design_feedback`, and `admin_users` tables. Ratings are validated by the feedback form, and the feedback table avoids foreign keys so it can be created alongside older XAMPP database schemas. It also inserts nine categories and sixteen sample designs priced from ₹500 to ₹10,000.
6. In phpMyAdmin, expand `mehndi_db`. You should see those six tables. Select `designs` and choose **Browse** to see the sample designs.
7. If you already imported an older `database.sql`, select `mehndi_db` and import the updated `database.sql` again to create the missing `design_feedback` table. Existing tables and sample records are retained.

If your existing database was created from an older schema that does not have the `designs.price` field, back up your data and migrate that database schema before using the site. The included `database.sql` creates new tables; it does not upgrade existing tables.

The database connection settings are in `includes/db.php`. They are configured for this XAMPP setup with host `localhost`, MySQL port `4306`, username `root`, and an empty password. If your MySQL port or root password differs, update `$port` or `$password` in that file.

## 3. Run the website

For this workspace, open:

```text
http://localhost/Mehandi1234/Mehndi_AI/
```

If you copied the project directly into `C:\xampp\htdocs`, open:

```text
http://localhost/Mehndi_AI/
```

The homepage should show the sample designs. If you see a database connection message, confirm MySQL is running, the database import completed, and `includes/db.php` has the correct local credentials.

## 4. Create the first design administrator

1. Open `http://localhost/Mehandi1234/Mehndi_AI/admin/create_admin.php` (adjust the URL if you copied the project to the standard htdocs folder).
2. Choose a username and a unique password of at least 12 characters. This admin is only allowed to maintain design and category content.
3. After the account is created, **delete `admin/create_admin.php` from the project folder** using File Explorer. The setup page also locks itself as soon as an admin exists, but removing it is the safer final step.
4. Open `/admin/login.php` and sign in. The dashboard lets the admin add, edit and delete designs, and add/edit/delete categories. A category in use by a design cannot be deleted.
5. Admin changes affect the same designs and categories displayed in the gallery and used for recommendations.

There is no default or shared admin password. The first account's password is stored using PHP `password_hash()`.

## 5. Try the main website features

### Register and log in

1. Choose **Login / Register** in the navigation, then **Create an account**.
2. Enter your name, email address, and a password of at least eight characters twice.
3. Log in with the email and password. Passwords are stored as hashes and checked with `password_verify()`.

### Test the AI-inspired recommendations

1. Choose **AI Recommendation**.
2. For the included example, select **Wedding**, **Bridal**, **Heavy**, **Full Hand**, and **Floral**.
3. Choose a budget range. Optionally enter a custom maximum from ₹500 to ₹10,000; when entered, it takes precedence over the chosen range and includes prices from ₹500 through that amount.
4. Click **Get AI Recommendations**. For example, **Garden of Vows** costs ₹5,000 and matches Wedding, Bridal, Heavy, Full Hand and Floral; choose the ₹2,501–₹5,000 band for all five preference matches and the budget bonus.
5. The explanation under each result lists which preferences and budget matched. Each of the five design preferences contributes **+2 points**; an in-budget design contributes **+3 points**. Results are scored out of 13 possible points, displayed as a percentage, and sorted highest first.
6. Try changing an answer or budget to see the ranking change. The feature is a transparent rule-based system, not machine learning.
7. Customer ratings add up to two priority points, helping higher-rated designs rank ahead when preference and budget matches are close.

### Search, categories and design details

1. Open **Designs**. Search filters the displayed results by name, category, style or occasion as you type.
2. Choose a category to filter the gallery, or submit a search. The category list comes from the database.
3. Select **View details** to see the large image, price, description, style, occasion, complexity, area and type.

### Feedback and ratings

1. Log in, open a design's details page, and choose **Rate this design** or **Write a review**.
2. Select one star (Poor) through five stars (Excellent) and optionally add a comment up to 1,000 characters.
3. Each account can keep one review per design; submitting again updates that review. Reviews show the user's name and date, and the details page displays the average and review count.

### Save and remove favorites

1. Log in first. In the gallery, click the heart beside a design, or open its detail page and choose **Add to favorites**.
2. Open **Favorites** to see saved designs.
3. Click the filled heart and confirm to remove a favorite. Favorites are saved in MySQL per registered user.

### Add your own designs

1. Log in to the admin area and select **Add a design**.
2. Enter a name, description and numeric price from ₹500 to ₹10,000; choose a category and select values for style, occasion, complexity, application area and design type.
3. Upload a JPG, PNG or WebP image (up to 5 MB), check the preview and save. The image is stored in `uploads/`; its path and matching attributes are stored in the `designs` table.
4. Return to the gallery and recommendation form to test the new design. Accurate preference values are important because recommendation matching compares those stored fields with the user's selections.

### Optional style analyzer

Open **Style Analyzer**, choose an image and select visual clues. The image preview stays in the browser and is not uploaded. The suggestion is calculated from the checklist answers using simple rules. It is not automatic image analysis and does not pretend to be a trained AI model.

## 6. Project files: location, purpose and how to test

All paths below are relative to `C:\xampp1\htdocs\Mehandi1234\Mehndi_AI`. The complete code is in each named file; keep the folder structure intact so PHP includes, CSS, JavaScript and images resolve correctly.

### Website PHP files

| File name | Where | What it does | How to test |
| --- | --- | --- | --- |
| `index.php` | Project root | Home page, sample designs, categories and project introduction. | Open the project URL. |
| `designs.php` | Project root | Searchable/filterable design gallery with prices and favorite buttons. | Search `Wedding`, `Arabic` or a sample title. |
| `design_details.php` | Project root | Full details and price for the selected design. | Click **View details** on any card. |
| `feedback.php` | Project root | Saves or updates a signed-in user's 1–5-star rating and optional comment for a design. | Submit feedback from a design details page. |
| `ai_recommendation.php` | Project root | Validates preferences and budget, scores exact preference matches (+2) and in-budget designs (+3), and uses ratings as a secondary priority factor. | Run the wedding example with the ₹2,501–₹5,000 budget. |
| `favorites.php` | Project root | Shows the signed-in user's saved designs. | Log in and save a gallery design. |
| `favorite_action.php` | Project root | Validates and adds/removes a favorite with a prepared SQL statement. | Use the heart buttons; do not open this action URL directly. |
| `register.php` | Project root | Validates a registration and stores a securely hashed password. | Register a new email, then check `users` in phpMyAdmin. |
| `login.php` | Project root | Verifies a password and starts a PHP session. | Log in with the account you just registered. |
| `logout.php` | Project root | Ends the signed-in user's session. | Use **Log out**, then open Favorites. |
| `style_analyzer.php` | Project root | Optional local image preview and honest checklist-based style suggestion. | Select an image, tick clues and click **Suggest a style**. |

### Shared PHP files and administrator files

| File name | Where | What it does | How to test |
| --- | --- | --- | --- |
| `includes/db.php` | `includes` | Reusable MySQLi connection for `mehndi_db`. | Load any page that queries designs. |
| `includes/functions.php` | `includes` | HTML escaping, sessions, CSRF checks, flash messages, login checks, image upload validation and fallback images when a design's referenced file is missing. | Open the gallery with a missing image path in the database. |
| `includes/header.php` | `includes` | Shared website navigation and status messages. | Check the navigation on any normal site page. |
| `includes/footer.php` | `includes` | Shared footer and closing page markup. | Scroll to the bottom of a normal site page. |
| `admin/create_admin.php` | `admin` | Creates the first password-hashed admin only when no admin exists. | Visit once during setup, then remove this file. |
| `admin/login.php` | `admin` | Separate password-verified admin login. | Sign in using the first admin account. |
| `admin/dashboard.php` | `admin` | Lists design content and links to add/edit/delete actions. | Open after admin login. |
| `admin/add_design.php` | `admin` | Validates design fields, numeric ₹500–₹10,000 price and image uploads, then adds a gallery record. | Add a sample design and find it in the gallery. |
| `admin/edit_design.php` | `admin` | Updates design details and price and optionally replaces its image. | Use **Edit** in the admin dashboard. |
| `admin/delete_design.php` | `admin` | Deletes a design after a POST request and confirmation. | Use **Delete** in the admin dashboard. |
| `admin/categories.php` | `admin` | Adds, edits and deletes gallery categories. | Add a temporary category, then remove it if unused. |
| `admin/logout.php` | `admin` | Ends only the admin login session. | Select **Log out** in the admin navigation. |
| `admin/_header.php` | `admin` | Shared admin navigation and enforces the admin session. | Open any admin page after signing in. |
| `admin/_footer.php` | `admin` | Shared admin page footer. | Scroll to the bottom of an admin page. |

### Database, design assets and presentation

| File name | Where | What it does | How to test |
| --- | --- | --- | --- |
| `database.sql` | Project root | Creates the database and tables for designs, favorites and feedback with validation constraints, then inserts sample categories and designs. | Import it in phpMyAdmin and browse the `designs` or `design_feedback` table. |
| `css/style.css` | `css` | Responsive colors, layouts, cards, forms, mobile menu and motion styles. | Resize the browser or use its mobile device view. |
| `js/script.js` | `js` | Mobile navigation, client-side validation, live search, category filtering, confirmation prompts, image preview and optional analyzer rules. | Try those interactions on their relevant pages. |
| `images/motif-floral.svg` | `images` | Original illustrative sample design image. | View a floral sample in the gallery. |
| `images/motif-mandala.svg` | `images` | Original illustrative mandala sample image. | View a mandala sample in the gallery. |
| `images/motif-vine.svg` | `images` | Original illustrative vine sample image. | View an Arabic/vine sample in the gallery. |
| `images/motif-geometric.svg` | `images` | Original illustrative geometric sample image. | View the geometric sample in the gallery. |
| `images/popular-design-1.png`–`images/popular-design-6.png` | `images` | Mehndi photos shown in Popular designs and used as fallbacks for missing database image files. | Open Popular designs or the Designs gallery. |
| `uploads/.htaccess` | `uploads` | Disables directory listing and blocks common executable script extensions in uploads. | Upload a supported image through the admin form. |
| `uploads/.gitkeep` | `uploads` | Keeps the initially empty uploads folder in the project. | Confirm the folder exists before uploading. |

## 7. If an image upload fails

The application accepts JPG, PNG and WebP files up to 5 MB. In XAMPP, PHP's `upload_max_filesize` and `post_max_size` settings must also permit the upload. If necessary, open the active `php.ini` from the XAMPP Control Panel (**Apache → Config → PHP (php.ini)**), set `upload_max_filesize = 8M` and `post_max_size = 10M`, save, and restart Apache. Never upload PHP or other executable files as design images.

## 8. Beginner notes

- PHP files must be opened through an `http://localhost/...` URL, not as local files.
- Keep Apache and MySQL running while browsing the project.
- `database.sql` is the database structure and starter content; do not rename its columns unless you also update the PHP queries.
- Do not put real user data or a production website on this local demo without additional deployment security work.
- Sample SVG illustrations are decorative project assets. Replace them with images you own or are licensed to use.
