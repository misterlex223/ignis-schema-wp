/**
 * Generated TypeScript types for Tests
 * @generated from schema: test.yaml
 */

// ACF Fields Interface
export interface TestACF {
  /** Test Field
   * This is a test field for the schema system
   */
  test_field?: string;
  /** Test Number
   */
  test_number?: number;
}

// WordPress Post Interface
export interface Test {
  id: number;
  date: string;
  date_gmt: string;
  modified: string;
  modified_gmt: string;
  slug: string;
  status: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  type: 'test';
  link: string;
  title: {
    rendered: string;
  };
  content: {
    rendered: string;
    protected: boolean;
  };
  featured_media: number;
  acf: TestACF;
  _links: {
    self: Array<{ href: string }>;
    collection: Array<{ href: string }>;
  };
}

// API Response Types
export type TestResponse = Test;
export type TestListResponse = Test[];

// Create/Update Request Type
export interface TestCreateRequest {
  title: string;
  status?: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  content?: string;
  acf?: Partial<TestACF>;
}

export type TestUpdateRequest = Partial<TestCreateRequest>;

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