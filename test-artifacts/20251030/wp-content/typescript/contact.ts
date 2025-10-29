/**
 * Generated TypeScript types for Contacts
 * @generated from schema: contact.yaml
 */

// ACF Fields Interface
export interface ContactACF {
  /** Full Name
   * Enter the contact's full name
   */
  contact_name: string;
  /** Email Address
   */
  contact_email: string;
  /** Phone Number
   */
  contact_phone?: string;
  /** Department
   */
  contact_department: 'engineering' | 'marketing' | 'sales' | 'operations' | 'hr' | 'finance';
  /** Position/Title
   */
  contact_position?: string;
  /** Company
   */
  contact_company?: string;
  /** Biography
   */
  contact_bio?: string;
  /** Internal Notes
   */
  contact_notes?: string;
  /** Primary Contact
   */
  is_primary_contact?: boolean;
  /** Status
   */
  contact_status?: 'active' | 'inactive' | 'pending';
  /** Social Media Links
   */
  social_links?: {social_platform?: 'linkedin' | 'twitter' | 'github' | 'facebook' | 'instagram'; social_url?: string}[];
  /** Related Contacts
   * Link to related contacts (colleagues, team members, etc.)
   */
  related_contacts?: Contact;
}

// WordPress Post Interface
export interface Contact {
  id: number;
  date: string;
  date_gmt: string;
  modified: string;
  modified_gmt: string;
  slug: string;
  status: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  type: 'contact';
  link: string;
  title: {
    rendered: string;
  };
  content: {
    rendered: string;
    protected: boolean;
  };
  featured_media: number;
  acf: ContactACF;
  _links: {
    self: Array<{ href: string }>;
    collection: Array<{ href: string }>;
  };
}

// API Response Types
export type ContactResponse = Contact;
export type ContactListResponse = Contact[];

// Create/Update Request Type
export interface ContactCreateRequest {
  title: string;
  status?: 'publish' | 'future' | 'draft' | 'pending' | 'private';
  content?: string;
  acf?: Partial<ContactACF>;
}

export type ContactUpdateRequest = Partial<ContactCreateRequest>;

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