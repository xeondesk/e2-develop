# Legacy E2 Migration

The existing 63-file PHP project becomes a legacy/reference layer.

Map:
e2.php → content
e2edit.php → editor
e2comments.php → comments
e2categories.php → taxonomy
e2login.php/e2register.php → identity
e2upload.php → media
e2template.php → themes
e2options.php → settings
xmlrpc/trackback/pingback → legacy compatibility

Migration pipeline:
legacy DB → adapter → normalizer → Nexo model → Nexo DB.

Do not rewrite the legacy system in place.
