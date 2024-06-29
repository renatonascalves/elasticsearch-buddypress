# Elasticsearch BuddyPress

[![Project Status: Active.](https://www.repostatus.org/badges/latest/concept.svg)](https://www.repostatus.org/#concept)

Elasticsearch BuddyPress is an integration of the BuddyPress plugin with Elasticsearch using the common/popular plugins: [ElasticPress](https://github.com/10up/ElasticPress), [SearchPress](https://github.com/alleyinteractive/searchpress), and [VIP Enterprise Search](https://docs.wpvip.com/enterprise-search/).

## Overview

Currently, the goal of this plugin is purely educational. Joining my experience with BuddyPress and Elasticsearch to create something useful. But it is also mostly an experiment at this point. Not ready for live sites/communities, yet.

I'm currently working on the implementation design (by using an Adapter, also known as Wrapper, Design pattern), by adding support for the Groups components for the [ElasticPress](https://github.com/10up/ElasticPress) plugin.

## Requirements

* [PHP](https://www.php.net/) >= 8.3+
* [WordPress](https://wordpress.org/) >= 6.5+
* [BuddyPress](https://buddypress.org/) >= latest
* [ElasticPress](https://github.com/10up/ElasticPress) >= latest
* [Elasticsearch](https://www.elastic.co/) >= 7.15+

## Example

Here is an example of how to query groups using the `ep_integrate` parameter:

```php
$args = [ "ep_integrate" => true ];

BP_Groups_Group::get( $args );
```
