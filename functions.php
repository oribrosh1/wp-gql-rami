<?php
/**
 * Twenty Twenty-Two functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Two
 * @since Twenty Twenty-Two 1.0
 */
add_action( 'graphql_register_types', function() {


	class AttributesToReturn{
		public $attributeName;
		public $attributeValues;
		
		function __construct($attributeName,$attributeValues) {
			$this->attributeName = $attributeName;
			$this->attributeValues = $attributeValues;
		}
	}
	class AcfToReturn{
		public $acfDatabaseId;
		public $acfName;
		public $acfValue;
		
		function __construct($acfDatabaseId,$acfName,$acfValue) {
			$this->acfDatabaseId = $acfDatabaseId;
			$this->acfName = $acfName;
			$this->acfValue = $acfValue;
		}
	}
	class CategoriesToReturn{
		public $categoryName;
		public $categoryId;

		function __construct($categoryName,$categoryId) {
			$this->categoryName = $categoryName;
			$this->categoryId = $categoryId;
		}
	}
	class ProductToReturn{
		public $databaseID;
		public $modelName;
		public $description;
		public $price;
		public $images;
		public $categories;
		public $subcategories;
		public $likes;
		public $views;
		public $featured;
		public $attributes;
		
		function __construct($databaseID,$modelName,$description,$price,$images,$categories,$subcategories,$likes,$views,$featured,$attributes) {
			$this->databaseID = $databaseID;
			$this->modelName = $modelName;
			$this->description = $description;
			$this->price = $price;
			$this->images = $images;
			$this->categories = $categories;
			$this->subcategories = $subcategories;
			$this->views = $views;
			$this->likes = $likes;
			$this->featured = $featured;
			$this->attributes = $attributes;
		}
	}
	
	register_graphql_object_type( 'ProductAttributeToReturnType', [
		'description' => 'product attributes to return',
		'fields' => [
		  'attributeName' => [
			  'type' => 'String',
			  'description' => 'attribute name',
		  ],
		  'attributeValues' => [
			  'type' => [ 'list_of' => 'String' ],
			  'description' => ' attribute values',
		  ]
		],
	  ] );

	register_graphql_object_type( 'ProductCategoryToReturnType', [
		'description' => 'product attributes to return',
		'fields' => [
		  'categoryName' => [
			  'type' => 'String',
			  'description' => 'category name',
		  ],
		  'categoryId' => [
			  'type' => 'String' ,
			  'description' => ' category id',
		  ]
		],
	  ] );

	register_graphql_object_type( 'ProductACFToReturnType', [
		'description' => 'product acf to return',
		'fields' => [
		  'acfDatabaseId' => [
			  'type' => 'String',
			  'description' => 'acf databaseId',
		  ],
		  'acfName' => [
			  'type' => 'String',
			  'description' => 'acf name',
		  ],
		  'acfValue' => [
			  'type' => 'Integer' ,
			  'description' => ' acf values',
		  ]
		],
	  ] );
	// This registers a connection to the Schema at the root of the Graph
	// The connection field name is "popularPosts"
	register_graphql_connection( [
		'fromType'           => 'RootQuery',
		'toType'             => 'Product',
		'fromFieldName'      => 'popularProducts', // This is the field name that will be exposed in the Schema to query this connection by
		'connectionTypeName' => 'RootQueryToPopularProductsConnection',
		'connectionArgs'     => \WPGraphQL\Connection\PostObjects::get_connection_args(), // This adds Post connection args to the connection
		'resolve'            => function( $root, $args, \WPGraphQL\AppContext $context, $info ) {

			$resolver = new \WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver( $root, $args, $context, $info );
			global $wpdb;

			// Note, these args will override anything the user passes in as { where: { ... } } args in the GraphQL Query
			$resolver->set_query_arg( 'meta_key', 'likes' );
			$resolver->set_query_arg( 'orderby', 'meta_value_num' );
			$resolver->set_query_arg( 'order', 'DESC' );

			return $resolver->get_connection();
		}
	] );

	// This registers a field to the "Post" type so we can query the "viewCount" and see the value of which posts have the most views
	register_graphql_field( 'Product', 'likes', [
		'type'    => 'Int',
		'resolve' => function( $post ) {
			return get_post_meta( $post->databaseId, 'likes', true );
		}
	] );

	// This registers a field to the "Post" type so we can query the "viewCount" and see the value of which posts have the most views
	register_graphql_field( 'Product', 'views', [
		'type'    => 'Int',
		'resolve' => function( $post ) {
			return get_post_meta( $post->databaseId, 'views', true );
		}
	] );

	

	register_graphql_object_type('ProductToReturnType', [
		'description' =>'product to return type',
		'fields' => [
            'databaseID' => [
                'type' => 'String',
                'description' => 'db id',
            ],
			'modelName' => [
                'type' => 'String',
                'description' => 'product modelName',
            ],
			'description' => [
                'type' => 'String',
                'description' => 'product description',
            ],
			'price' => [
                'type' => 'String',
                'description' => 'product price',
            ],
			'images' => [
                'type' => [ 'list_of' => 'String' ],
                'description' => 'product images',
            ],
			'categories' => [
                'type' => [ 'list_of' => 'ProductCategoryToReturnType' ],
                'description' => 'product categories',
            ],
			'subcategories' => [
                'type' => [ 'list_of' => 'ProductCategoryToReturnType' ],
                'description' => 'product subcategories',
            ],
			'likes' => [
                'type' => 'ProductACFToReturnType',
                'description' => 'product likes',
            ],
			'views' => [
                'type' => 'ProductACFToReturnType',
                'description' => 'product views',
            ],
			'featured' => [
                'type' => 'String',
                'description' => 'product featured',
            ],
			'attributes' => [
                'type' => [ 'list_of' => 'ProductAttributeToReturnType' ],
                'description' => 'product attributes',
            ],
        ],
	]);
// } );
register_graphql_enum_type( 'OrderType', [
	'description' => 'DESC or ASC',
	'values' => [
		'DESC' => [
			'value' => 'DESC'
		],
		'ASC' => [
			'value' => 'ASC'
		]
	],
] );
register_graphql_enum_type( 'OrderByType', [
	'description' => 'DESC or ASC',
	'values' => [
		'likes' => [
			'value' => 'likes'
		],
		'views' => [
			'value' => 'views'
		],
		'price' => [
			'value' => '_price'
		],
	],
] );
	register_graphql_field( 'RootQuery', 'sortProducts', [
	  'type' => [ 'list_of' => 'ProductToReturnType' ],
	  'args' => [
		'orderType' => [
		  'type' => 'OrderType',
		],
		'orderBy' => [
			'type' => 'OrderByType',
		  ],
	  ],
	  'description' => 'test',
	  'resolve' => function( $root, $args, $context, $info ) {
			global $wpdb;
			$orderType;
			$orderBy;
			if ( isset( $args['orderType'] ) ) {
				$orderType = 'DESC' === strtoupper( $args['orderType'] ) ? 'DESC' : 'ASC';
			}
			if ( isset( $args['orderBy'] ) ) {
				$orderBy = $args['orderBy'];
			}
			$wc_products_query = $wpdb->prepare( 
			"SELECT wp_posts.ID as id, wp_posts.post_name,wp_posts.post_content, 
			wp_postmeta.meta_value as metaVal
			FROM wp_posts
			LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id  ) 
			WHERE wp_posts.post_type = 'product'  
			AND wp_posts.post_status = 'publish'
			AND wp_postmeta.meta_key = %s
			GROUP BY wp_posts.ID
			ORDER BY cast(metaVal as unsigned) $orderType"
			,$orderBy);
			$wc_products = $wpdb->get_results($wc_products_query);
			return getProductByTypes($wc_products);		
	  }
	] );

	register_graphql_field( 'RootQuery', 'saleProducts', [
		'type' => [ 'list_of' => 'ProductToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT wp_posts.ID as id,wp_posts.post_name,wp_posts.post_content,
				wp_postmeta.meta_value as regPrice, mt1.meta_value as salePrice
				FROM wp_posts
				LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id AND wp_postmeta.meta_key = '_regular_price') 
				LEFT JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id AND  mt1.meta_key = '_sale_price' AND mt1.meta_value !='') 
				WHERE wp_posts.post_type = 'product'  
				AND wp_posts.post_status = 'publish'
				GROUP BY wp_posts.ID
				ORDER BY cast(regPrice as unsigned) - cast(salePrice as unsigned) DESC";
			  $wc_products = $wpdb->get_results($wc_products_query);
			  return getProductByTypes($wc_products);		
		}
	  ] );

	  register_graphql_field( 'RootQuery', 'mostViewedProducts', [
		'type' => [ 'list_of' => 'ProductToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT wp_posts.ID as id,wp_posts.post_name,wp_posts.post_content,
				wp_postmeta.meta_value as views
				FROM wp_posts
				LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id AND wp_postmeta.meta_key = 'views') 
				WHERE wp_posts.post_type = 'product'  
				AND wp_posts.post_status = 'publish'
				GROUP BY wp_posts.ID
				ORDER BY cast(views as unsigned) DESC";
			  $wc_products = $wpdb->get_results($wc_products_query);
			  return getProductByTypes($wc_products);		
		}
	  ] );

	  register_graphql_field( 'RootQuery', 'mostLikedProducts', [
		'type' => [ 'list_of' => 'ProductToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT wp_posts.ID as id,wp_posts.post_name,wp_posts.post_content,
				wp_postmeta.meta_value as likes
				FROM wp_posts
				LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id AND wp_postmeta.meta_key = 'likes') 
				WHERE wp_posts.post_type = 'product'  
				AND wp_posts.post_status = 'publish'
				GROUP BY wp_posts.ID
				ORDER BY cast(likes as unsigned) DESC";
			  $wc_products = $wpdb->get_results($wc_products_query);
			  return getProductByTypes($wc_products);		
		}
	  ] );

	  register_graphql_field( 'RootQuery', 'mostLikedAndViewedProducts', [
		'type' => [ 'list_of' => 'ProductToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT wp_posts.ID as id,wp_posts.post_name,wp_posts.post_content,
				wp_postmeta.meta_value as likes, mt1.meta_value as views
				FROM wp_posts
				LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id AND wp_postmeta.meta_key = 'likes') 
				LEFT JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id AND  mt1.meta_key = 'views') 
				WHERE wp_posts.post_type = 'product'  
				AND wp_posts.post_status = 'publish'
				GROUP BY wp_posts.ID
				ORDER BY cast(likes as unsigned) DESC, cast(views as unsigned) DESC";
			  $wc_products = $wpdb->get_results($wc_products_query);
			  return getProductByTypes($wc_products);		
		}
	  ] );

	  register_graphql_field( 'RootQuery', 'mostLikedFromViewedProducts', [
		'type' => [ 'list_of' => 'ProductToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT wp_posts.ID as id,wp_posts.post_name,wp_posts.post_content,
				wp_postmeta.meta_value as likes, mt1.meta_value as views
				FROM wp_posts
				LEFT JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id AND wp_postmeta.meta_key = 'likes') 
				LEFT JOIN wp_postmeta AS mt1 ON ( wp_posts.ID = mt1.post_id AND  mt1.meta_key = 'views') 
				WHERE wp_posts.post_type = 'product'  
				AND wp_posts.post_status = 'publish'
				GROUP BY wp_posts.ID
				ORDER BY cast(views as unsigned) - cast(likes as unsigned) ASC, cast(likes as unsigned) DESC";
			  $wc_products = $wpdb->get_results($wc_products_query);
			  return getProductByTypes($wc_products);		
		}
	  ] );


	  //todo:
	  //all parents categories
	  register_graphql_field( 'RootQuery', 'parentCategories', [
		'type' => [ 'list_of' => 'ProductCategoryToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT TOP 10 category_terms.name as cname, category_tax.term_taxonomy_id as id, category_tax.parent
				  FROM $wpdb->term_taxonomy as category_tax
				  INNER JOIN $wpdb->terms as category_terms 
				  ON( category_tax.term_id = category_terms.term_id 
				  AND category_tax.taxonomy = 'product_cat' 
				  AND category_tax.parent = '0')";
			  $wc_categories = $wpdb->get_results($wc_products_query);
			  $categories = array();
			  foreach ( $wc_categories as $wc_category ) {
				$categories[] = new CategoriesToReturn($wc_category->cname,$wc_category->id);
			  }
			  return $categories;		
		}
	  ] );
	  register_graphql_field( 'RootQuery', 'allpParentCategories', [
		'type' => [ 'list_of' => 'ProductCategoryToReturnType' ],
		'description' => 'test',
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
		 $wc_products_query = 
			  	"SELECT category_terms.name as cname, category_tax.term_taxonomy_id as id, category_tax.parent
				  FROM $wpdb->term_taxonomy as category_tax
				  INNER JOIN $wpdb->terms as category_terms 
				  ON( category_tax.term_id = category_terms.term_id 
				  AND category_tax.taxonomy = 'product_cat' 
				  AND category_tax.parent = '0')
				  OFFSET 10 ROWS";
			  $wc_categories = $wpdb->get_results($wc_products_query);
			  $categories = array();
			  foreach ( $wc_categories as $wc_category ) {
				$categories[] = new CategoriesToReturn($wc_category->cname,$wc_category->id);
			  }
			  return $categories;		
		}
	  ] );

	  //all sub categories from parent
	  register_graphql_field( 'RootQuery', 'subCategoriesByParent', [
		'type' => [ 'list_of' => 'ProductCategoryToReturnType' ],
		'description' => 'test',
		'args' => [
			'parentId' => [
			  'type' => 'String',
			],
		  ],
		'resolve' => function( $root, $args, $context, $info ) {
			  global $wpdb;
			  $parentId;
			  if ( isset( $args['parentId'] ) ) {
				$parentId = $args['parentId'];
			}
			$wc_products_query = $wpdb->prepare( 
			  	"SELECT category_terms.name as cname, category_tax.term_taxonomy_id as id, category_tax.parent
				  FROM $wpdb->term_taxonomy as category_tax
				  INNER JOIN $wpdb->terms as category_terms ON( category_tax.term_id = category_terms.term_id 
				  AND category_tax.taxonomy = 'product_cat' AND category_tax.parent = %s)",$parentId);
			  $wc_categories = $wpdb->get_results($wc_products_query);
			  $categories = array();
			  foreach ( $wc_categories as $wc_category ) {
				$categories[] = new CategoriesToReturn($wc_category->cname,$wc_category->id);
			  }
			  return $categories;		
		}
	  ] );
	  //all related products from this category



	  //mutations from down here 
	  register_graphql_enum_type( 'LikesOrViewsEnum', [
		'description' => 'DESC or ASC',
		'values' => [
			'likes' => [
				'value' => 'likes'
			],
			'views' => [
				'value' => 'views'
			],
		],
	] );

		register_graphql_mutation( 'increaseLikesOrViewsToProduct', [

			'inputFields'         => [
				'metaId' => [
					'type' => 'String',
					'description' => 'the meta id of the meta field of the likes/views of the product',
				],
				'type' => [
					'type' => 'LikesOrViewsEnum',
					'description' => 'the meta field of the likes or views of the product',
				],
			],
			'outputFields'        => [
				'output' => [
					'type' => 'String',
					'description' => __( 'Description of the output field', 'your-textdomain' ),
					'resolve' => function( $payload, $args, $context, $info ) {
								return isset( $payload['output'] ) ? $payload['output'] : null;
					}
				]
			],
			'mutateAndGetPayload' => function( $input, $context, $info ) {
				// Do any logic here to sanitize the input, check user capabilities, etc
				$output;
				global $wpdb;
				if ( ! empty( $input['type'] ) && ! empty( $input['metaId'] )) {
					$muataion =  $wpdb->prepare("
						UPDATE wp_postmeta
						SET meta_value = cast(meta_value as unsigned) + 1
						WHERE meta_key = %s AND meta_id = %s", $input['type'], $input['metaId']);
					$wpdb->query($muataion);
					$output = 'done!';
				}
				return [
					'output' => $output,
				];
			}
		] );

  });
  function getProductByTypes($wc_products){
	$images = array();
	$attributes = array();
	$res = array();

	foreach ( $wc_products as $wc_product ) {
		$ID = $wc_product->id;
		$product = wc_get_product( $ID );
		$product_data = $product->get_data();
		$name = $product_data['name'];
		$price = $product_data['price'];
		$featured;
				if(empty($product_data['featured'])){
					$featured = 'false';
				}else{
					$featured = 'true';
				}
		$desc = $product_data['description'];
		$first_image = wp_get_attachment_image_src( get_post_thumbnail_id( $ID ), 'single-post-thumbnail' );
		$attachment_ids = $product->get_gallery_image_ids();
		if(!empty($first_image)){
			$images[] = $first_image[0];
		}
		foreach( $attachment_ids as $attachment_id ) 
		{
		$images[] = wp_get_attachment_url( $attachment_id );
		}
		$product_cat_terms = get_the_terms( $ID, 'product_cat' );
		$sub_categories = array();
		$categories = array();
		foreach ($product_cat_terms as $term) {
			if(!empty($term->parent)){		
				$parent_term = get_term( $term->parent );
				$product_cat_parent_id = $parent_term->term_id;
				$product_cat_parent_name = $parent_term->name;
				$categories[] = new CategoriesToReturn($product_cat_parent_name, $product_cat_parent_id);
			}
			$category_id = $term->term_id;
			$category_name = $term->name;
			$sub_categories[] = new CategoriesToReturn($category_name, $category_id);
		}
		
		$product_attributes = $product->get_attributes();
		
			
		foreach ( $product_attributes as $attribute ){
			$attribute_name = $attribute->get_taxonomy(); // The taxonomy slug name
			$attribute_terms = $attribute->get_terms(); // The terms
			$attribute_values = array();
			if(is_array($attribute_terms)){
				foreach ( $attribute_terms as $term ){
					$attribute_values[] = $term->name;
				}
			}
			if(is_object($attribute_terms)){
				
					$attribute_values[] = $attribute_terms->name;
			}
			$attributes[] = new AttributesToReturn($attribute_name,$attribute_values);
		}
		$likes;
		$views;
		$meta_data_array = $product_data['meta_data'];
		
		foreach($meta_data_array as $meta){
			if($meta->key == "likes"){
				if(empty($meta->value)){
					$likes = new AcfToReturn($meta->id,$meta->key,0);
				}else{

					$likes = new AcfToReturn($meta->id,$meta->key,$meta->value);
				}
			}
			if($meta->key == "views"){
				if(empty($meta->value)){
					$views = new AcfToReturn($meta->id,$meta->key,0);
				}else{

					$views = new AcfToReturn($meta->id,$meta->key,$meta->value);
				}
			}
		}

		
		$res[] = new ProductToReturn($ID,$name,$desc,$price,$images,$categories,$sub_categories,$likes,$views,$featured,$attributes);
	}

	return $res;
}