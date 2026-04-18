export type UserRole = 'super_admin' | 'editor' | 'viewer'

export interface AuthUser {
    id: number
    name: string
    email: string
    role: UserRole
    locale: string
    last_login_at: string | null
}

export interface FlashMessages {
    success: string | null
    error: string | null
}

export interface SharedProps {
    app: {
        name: string
    }
    auth: AuthUser | null
    flash: FlashMessages
    errors: Record<string, string>
    ziggy: {
        location: string
        [key: string]: unknown
    }
    [key: string]: unknown
}
