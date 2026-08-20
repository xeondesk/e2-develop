# Legacy E2

The existing legacy PHP CMS belongs here as a compatibility/reference layer.

Migration flow:
legacy database -> adapter -> normalizer -> Nexo domain -> Nexo database.

Do not mix legacy implementation details into Nexo Core.

---

## e2-develop (Original Project)

A Dockerized build of the **e2** blog engine (the cafelog.com / "b2" successor
that b2evolution and WordPress both descended from). The original project's
`e2-include/` engine has been reconstructed so the whole application runs on
modern PHP 8.4 + MySQL 8.

### Quick start

Requirements: Docker + Docker Compose.

```sh
cd legacy/e2
docker compose up -d --build
```

Wait for the DB to be ready, then open the installer:

1. http://localhost:8080/ and click through `e2install.php`.
   - The DB host is `db` (the compose service name), database `e2`,
     user `e2`, password `e2pass`.
   - The installer creates an admin account and prints a randomly generated
     password **on the page — copy it now** (it is not configurable).
2. Click "Start my weblog !" to load the blog.

### Admin

- Login page: http://localhost:8080/e2login.php (user `admin` + the random
  install password).
- The admin menu (write posts, categories, options, templates, team, profile)
  appears once logged in. Posting, comment moderation, and the Blogger API are
  covered below.

### Features verified working

- Blog front page, single posts, category archives, monthly archives, search,
  pagination, "all posts" view.
- Comments (post, list, popups, trackback/pingback popup links).
- RSS 0.92 / RDF / RSS 2.0 feeds.
- Archives sidebar (`e2archives.php`) and monthly calendar (`e2calendar.php`).
- Admin: post editing, categories, options, templates, team, profile.
- Blogger XML-RPC API (`xmlrpc.php`): `blogger.getUsersBlogs`, `getPost`,
  `getRecentPosts`, `newPost`, `editPost`, `deletePost`.
  Titles/categories are conveyed inside the post content as
  `<title>...</title><category>...</category>`.

### Configuration

- `e2config.php` — site URL, DB credentials, paths, options.
- `docker/php.ini` — PHP settings; `auto_prepend_file` loads the
  `mysql_*` → `mysqli` compatibility shim (`e2-include/compat.php`) before
  every request, which is what lets the old code run on PHP 8.4.
- `docker-compose.yml` — the MySQL service uses `--sql-mode=NO_ENGINE_SUBSTITUTION`
  because the original schema uses `DEFAULT '0000-00-00 00:00:00'` (invalid on
  MySQL 8 with the default strict mode).

### Notes

- No mail server is configured; `e2mail.php` (daily posting by email) needs
  one before it can be used.
- `mysql_*` functions, `$HTTP_*_VARS`, `ereg*`, `each()`, `preg_replace /e`,
  and `'' == 0` (PHP 8 removed/changed all of these) are handled by
  `e2-include/compat.php` and small fixes in the original presentation files.