export type UserRole = 'super_admin' | 'editor' | 'viewer'

export interface AuthUser {
    id: number
    name: string
    email: string
    role: UserRole
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
    ziggy: {
        location: string
        [key: string]: unknown
    }
}
