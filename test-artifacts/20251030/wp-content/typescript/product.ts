/**
 * Generated TypeScript types for Products
 * @generated from schema: product.yaml
 */

// ACF Fields Interface
export interface ProductACF {
  /** Product Name
   */
  product_name: string;
  /** SKU
   * Stock Keeping Unit - must be unique
   */
  sku: string;
  /** Short Description
   * Brief product description for listings
   */
  product_description_short?: string;
  /** Regular Price
   */
  price: number;
  /** Sale Price
   * Leave empty if not on sale
   */
  sale_price?: number;
  /** On Sale
   */
  on_sale?: boolean;
  /** Stock Quantity
   * Number of items in stock
   */
  stock_quantity?: number;
  /** In Stock
   */
  in_stock?: boolean;
  /** Low Stock Alert
   * Alert when stock falls below this number
   */
  low_stock_threshold?: number;
  /** Weight
   */
  product_weight?: number;
  /** Dimensions
   */
  product_dimensions?: {length?: number; width?: number; height?: number};
  /** Product Images
   * Upload product images (recommended: 1200x1200px)
   */
  product_images?: WPImage[];
  /** Product Video
   * YouTube or Vimeo video URL
   */
  product_video?: string;
  /** Product Specifications
   */
  specifications?: {spec_name?: string; spec_value?: string}[];
  /** Key Features
   */
  features?: {feature_icon?: 'star' | 'check' | 'shield' | 'lightning' | 'heart'; feature_title: string; feature_description?: string}[];
  /** Product Categories
   */
  product_category?: WPTerm[];
  /** Product Tags
   */
  product_tags?: number[];
  /** Brand
   */
  product_brand?: string;
  /** Brand Logo
   */
  brand_logo?: WPImage;
  /** Related Products
   * Select related or complementary products
   */
  related_products?: Product;
  /** Free Shipping
   */
  free_shipping?: boolean;
  /** Shipping Class
   */
  shipping_class?: 'standard' | 'express' | 'heavy' | 'fragile';
  /** Product Badge
   * Display a badge on the product
   */
  product_badge?: 'new' | 'bestseller' | 'limited' | 'exclusive';
  /** Featured Product
   * Show on homepage and featured collections
   */
  featured_product?: boolean;
  /** Warranty Information
   */
  warranty_info?: string;
  /** Care Instructions
   */
  care_instructions?: string;
}

// WordPress Post Interface
export interface Product {
  id: number;
  date: string;
  date_gmt: string;
  modified: string;
  modified_gmt: string;
  slug: string;
  status: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  type: 'product';
  link: string;
  title: {
    rendered: string;
  };
  content: {
    rendered: string;
    protected: boolean;
  };
  excerpt: {
    rendered: string;
    protected: boolean;
  };
  featured_media: number;
  acf: ProductACF;
  _links: {
    self: Array<{ href: string }>;
    collection: Array<{ href: string }>;
  };
}

// API Response Types
export type ProductResponse = Product;
export type ProductListResponse = Product[];

// Create/Update Request Type
export interface ProductCreateRequest {
  title: string;
  status?: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  content?: string;
  excerpt?: string;
  acf?: Partial<ProductACF>;
}

export type ProductUpdateRequest = Partial<ProductCreateRequest>;

// WordPress Helper Types
export interface WPImage {
  ID: number;
  id: number;
  title: string;
  filename: string;
  filesize: number;
  url: string;
  link: string;
  alt: string;
  author: string;
  description: string;
  caption: string;
  name: string;
  status: string;
  uploaded_to: number;
  date: string;
  modified: string;
  menu_order: number;
  mime_type: string;
  type: string;
  subtype: string;
  icon: string;
  width: number;
  height: number;
  sizes: {
    thumbnail?: string;
    'thumbnail-width'?: number;
    'thumbnail-height'?: number;
    medium?: string;
    'medium-width'?: number;
    'medium-height'?: number;
    large?: string;
    'large-width'?: number;
    'large-height'?: number;
    full?: string;
    'full-width'?: number;
    'full-height'?: number;
    [key: string]: string | number | undefined;
  };
}

export interface WPFile {
  ID: number;
  id: number;
  title: string;
  filename: string;
  filesize: number;
  url: string;
  link: string;
  author: string;
  description: string;
  caption: string;
  name: string;
  status: string;
  uploaded_to: number;
  date: string;
  modified: string;
  mime_type: string;
  type: string;
  subtype: string;
  icon: string;
}

export interface WPPost {
  ID: number;
  id: number;
  post_title: string;
  post_type: string;
  post_status: string;
  post_date: string;
  post_modified: string;
}

export interface WPTerm {
  term_id: number;
  name: string;
  slug: string;
  term_group: number;
  term_taxonomy_id: number;
  taxonomy: string;
  description: string;
  parent: number;
  count: number;
}

export interface WPUser {
  ID: number;
  user_firstname: string;
  user_lastname: string;
  user_email: string;
  user_login: string;
  user_nicename: string;
  display_name: string;
}