// Global type definitions

// API Response types
export interface ApiResponse<T = unknown> {
  data: T;
  success: boolean;
  message?: string;
}

// Common UI types
export interface BaseProps {
  className?: string;
  children?: React.ReactNode;
}
