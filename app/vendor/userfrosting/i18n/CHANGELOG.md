# Changelog

## v4.1.0
- `MessageTranslator` is now an extension of the base `Repository` class in userfrosting/support.
- Factored out the path building methods into `LocalePathBuilder`.
- Removed loading methods.  We now rely on the `ArrayFileLoader` class in userfrosting/support.

## v4.0.3
- Fixed Illuminate/config version requirement

## v4.0.2
- Moved dependencies to `require`
- Fixed standards compliance issues

## v4.0.1
- Replacing `str_replace` with Twig for placeholder replacement

## v4.0.0
- Initial release
