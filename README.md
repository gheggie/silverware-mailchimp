# SilverWare MailChimp Module

[![Latest Stable Version](https://poser.pugx.org/silverware/mailchimp/v/stable)](https://packagist.org/packages/silverware/mailchimp)
[![Latest Unstable Version](https://poser.pugx.org/silverware/mailchimp/v/unstable)](https://packagist.org/packages/silverware/mailchimp)
[![License](https://poser.pugx.org/silverware/mailchimp/license)](https://packagist.org/packages/silverware/mailchimp)

Provides an Ajax-powered [MailChimp][mailchimp] mailing list signup component and an API-driven mailing list dropdown field
for use with [SilverWare][silverware].

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Issues](#issues)
- [Contribution](#contribution)
- [Attribution](#attribution)
- [Maintainers](#maintainers)
- [License](#license)

## Requirements

- [SilverWare][silverware]
- [SilverWare Validator][silverware-validator]
- [MailChimp API][mailchimp-api]

## Installation

Installation is via [Composer][composer]:

```
$ composer require silverware/mailchimp
```

## Configuration

As with all SilverStripe modules, configuration is via YAML. Extensions and required JavaScript are defined
by `config.yml`. You may also modify the default timeout for the API via YAML:

```yml
SilverWare\MailChimp\API\MailChimpAPI:
  default_timeout: 10
```

Before this module can be used, you will need to create a [MailChimp API key][mailchimp-api-key].
Once you have created your API key, you can define it for your app in one of two ways:

- via site configuration (Settings tab)
- via YAML configuration file

This module will add a MailChimp tab to the Services tab under SilverWare
in your site settings. You can paste your API key into the 'MailChimp API Key' field.

Alternatively, you can add your API key to YAML config for your app:

```yml
SilverWare\MailChimp\API\MailChimpAPI:
  api_key: <paste the key here>
```

The key defined in site configuration will take precedence over the YAML key.

## Usage

This module provides a `MailChimpSignup` component and a `MailChimpListField` for use within forms.
The `MailChimpSignup` component can be added to your SilverWare templates and layouts using the CMS.

### MailChimp Signup Component

![MailChimp Signup Component](https://i.imgur.com/RZlt243.png)

The `MailChimpSignup` component is an Ajax-powered signup form for a particular mailing list within
your MailChimp account.

If you have added your API key correctly, when you create
a new `MailChimpSignup` component you will see a 'Mailing List' dropdown field. Select the
mailing list you would like users to subscribe to when using the form. You may also enter introductory
content which will appear above the signup form.

On the Options tab, you can choose whether the fields for first and last names are shown
and/or required.  You may also modify the messages that are shown to users
for certain events, such as when the user subscribes, is already subscribed, or encounters
an error.

If the form validates correctly, it will be submitted via Ajax and the appropriate message
will appear above the form. The controller will handle a regular POST submission if the Ajax submission does not work.

### MailChimp List Field

The `MailChimpListField` is an extension of a regular `DropdownField` that connects via the
MailChimp API and retrieves the mailing lists within your account. You may use it anywhere you require
a user to select a mailing list:

```php
use SilverWare\MailChimp\Forms\MailChimpListField;

$field = MailChimpListField::create(
    'MailingListID',
    'Choose a mailing list'
);
```

In order to improve performance and to also reduce traffic via the MailChimp API, the field will cache the
mailing list results for five minutes by default.  You can change this by calling the `setCacheTimeout()` method
and passing the number of seconds as an argument:

```php
$field->setCacheTimeout(60);
```

## Issues

Please use the [GitHub issue tracker][issues] for bug reports and feature requests.

## Contribution

Your contributions are gladly welcomed to help make this project better.
Please see [contributing](CONTRIBUTING.md) for more information.

## Attribution

- Makes use of [MailChimp API][mailchimp-api] by [Drew McLellan][drewm].

## Maintainers

[![Colin Tucker](https://avatars3.githubusercontent.com/u/1853705?s=144)](https://github.com/colintucker) | [![Praxis Interactive](https://avatars2.githubusercontent.com/u/1782612?s=144)](http://www.praxis.net.au)
---|---
[Colin Tucker](https://github.com/colintucker) | [Praxis Interactive](http://www.praxis.net.au)

## License

[BSD-3-Clause](LICENSE.md) &copy; Praxis Interactive

[silverware]: https://github.com/praxisnetau/silverware
[silverware-validator]: https://github.com/praxisnetau/silverware-validator
[composer]: https://getcomposer.org
[issues]: https://github.com/praxisnetau/silverware-mailchimp/issues
[mailchimp]: http://mailchimp.com
[mailchimp-api]: https://github.com/drewm/mailchimp-api
[mailchimp-api-key]: https://kb.mailchimp.com/integrations/api-integrations/about-api-keys
[drewm]: https://github.com/drewm
