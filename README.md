# WPGraphQL Tax Query

This plugin adds Tax_Query support to the WP GraphQL Plugin for postObject query args (WP_Query). 

## Pre-req's
Using this plugin requires having the <a href="https://github.com/wp-graphql/wp-graphql" target="_blank">WPGraphQL plugin</a> installed 
and activated. (version 0.0.15 or newer)

## Activating / Using
Activate the plugin like you would any other WordPress plugin. 

Once the plugin is active, the `taxQuery` argument will be available to any post object connectionQuery 
(posts, pages, custom post types, etc).

## Example Query
Below is an example Query using the taxQuery input on a `posts` query. (Go ahead and check things out in 
<a target="_blank" href="https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij?hl=en">GraphiQL</a>)

This will find `posts` that are in the category "graphql" OR tagged with "wordpress". 

```
query{
  posts(
    where: {
      taxQuery: {
        relation: OR,
        taxArray: [
          {
            terms: ["graphql"],
            taxonomy: CATEGORY,
            operator: IN,
            field: SLUG
          },
          {
            terms: ["wordpress"],
            taxonomy: POST_TAG,
            operator: IN,
            field: SLUG
          }
        ]
      }
  	}
  ){
    edges{
      cursor
      node{
        id
        postId
        link
        date
      }
    }
  }
}
```

The same query in PHP using WP_Query would look like: 

```
$args = [
    'tax_query' => [
        'relation' => 'OR',
        [
            'terms' => ['graphql'],
            'taxonomy' => 'category',
            'operator' => 'IN',
            'field' => 'slug',
        ],
        [
            'terms' => ['wordpress'],
            'taxonomy' => 'post_tag',
            'operator' => 'IN',
            'field' => 'slug',
        ],
    ],
];

new WP_Query( $args );
```
