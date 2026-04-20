export type UserRole = "super_admin" | "editor" | "viewer";
export type ThemeMode = "light" | "dark" | "system";

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    locale: string;
    last_login_at: string | null;
    theme_mode: ThemeMode;
    timezone: string;
}

export interface FlashMessages {
    success: string | null;
    error: string | null;
}

export interface SharedProps {
    app: {
        name: string;
    };
    auth: AuthUser | null;
    flash: FlashMessages;
    errors: Record<string, string>;
    ziggy: {
        location: string;
        [key: string]: unknown;
    };
    [key: string]: unknown;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt: string | null;
    status: "draft" | "published";
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    published_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Post extends Page {
    featured_image: string | null;
    category_id: number | null;
    category: Category | null;
    tags: Tag[];
}
