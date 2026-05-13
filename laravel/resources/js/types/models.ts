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
    parent: Category | null;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
}

export interface Page {
    id: number;
    user_id: number;
    title: string;
    slug: string;
    content: string;
    content_json: unknown;
    excerpt: string | null;
    status: "draft" | "published";
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    author: AuthUser;
    parent_id: number | null;
    parent: Pick<Page, "id" | "slug" | "title"> | null;
}

export interface Post extends Page {
    featured_image: string | null;
    category_id: number | null;
    category: Category | null;
    tags: Tag[];
}

export interface Paginated<T> {
    data: T[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface RecentPost {
    id: number;
    title: string;
    status: "draft" | "published";
    published_at: string | null;
    created_at: string;
    user_id: number;
    author: AuthUser;
}

export interface DashboardStats {
    posts: { total: number; published: number; draft: number };
    pages: { total: number; published: number; draft: number };
    categories: number;
    tags: number;
    recent_posts: RecentPost[];
}
