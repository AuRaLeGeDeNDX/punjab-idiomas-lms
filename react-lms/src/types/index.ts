// Core entity types
export interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  role: 'student' | 'teacher' | 'admin';
  createdAt: string;
  updatedAt: string;
}

export interface Course {
  id: string;
  title: string;
  description: string;
  thumbnail?: string;
  teacherId: string;
  teacher: User;
  category: string;
  difficultyLevel: 'beginner' | 'intermediate' | 'advanced';
  durationHours: number;
  maxStudents?: number;
  enrollmentStartDate?: string;
  enrollmentEndDate?: string;
  courseStartDate?: string;
  courseEndDate?: string;
  isPublished: boolean;
  isFeatured: boolean;
  enrollmentCount: number;
  modules: Module[];
  progress?: number; // For students
  createdAt: string;
  updatedAt: string;
}

export interface Module {
  id: string;
  courseId: string;
  title: string;
  description?: string;
  orderIndex: number;
  type: 'lesson' | 'assignment' | 'quiz' | 'discussion';
  isPublished: boolean;
  subpages: Subpage[];
  progress?: number; // For students
  createdAt: string;
  updatedAt: string;
}

export interface Subpage {
  id: string;
  moduleId: string;
  title: string;
  description?: string;
  orderIndex: number;
  isActive: boolean;
  contents: Content[];
  exercises: Exercise[];
  assignments: Assignment[];
  progress?: number; // For students
  createdAt: string;
  updatedAt: string;
}

export interface Content {
  id: string;
  subpageId: string;
  title: string;
  type: 'text' | 'video' | 'audio' | 'pdf' | 'image' | 'link';
  content: string; // HTML content or file URL
  orderIndex: number;
  isActive: boolean;
  isVisibleToStudents: boolean;
  metadata?: {
    duration?: number; // For video/audio
    fileSize?: number;
    mimeType?: string;
  };
  createdAt: string;
  updatedAt: string;
}

export interface Exercise {
  id: string;
  subpageId: string;
  title: string;
  description?: string;
  type: 'multiple_choice' | 'true_false' | 'short_answer' | 'essay';
  questions: Question[];
  orderIndex: number;
  isActive: boolean;
  maxAttempts?: number;
  timeLimit?: number; // in minutes
  createdAt: string;
  updatedAt: string;
}

export interface Question {
  id: string;
  exerciseId: string;
  question: string;
  type: 'multiple_choice' | 'true_false' | 'short_answer' | 'essay';
  options?: string[]; // For multiple choice
  correctAnswer?: string | string[];
  points: number;
  orderIndex: number;
  explanation?: string;
}

export interface Assignment {
  id: string;
  subpageId: string;
  title: string;
  description: string;
  instructions: string;
  dueDate?: string;
  maxScore: number;
  submissionType: 'file' | 'text' | 'url';
  allowLateSubmission: boolean;
  isPublished: boolean;
  orderIndex: number;
  submissions?: Submission[];
  createdAt: string;
  updatedAt: string;
}

export interface Submission {
  id: string;
  assignmentId: string;
  studentId: string;
  student: User;
  content?: string;
  fileUrl?: string;
  submittedAt: string;
  grade?: Grade;
  status: 'draft' | 'submitted' | 'graded' | 'returned';
}

export interface Grade {
  id: string;
  submissionId: string;
  teacherId: string;
  teacher: User;
  score: number;
  maxScore: number;
  feedback?: string;
  isPublished: boolean;
  gradedAt: string;
}

export interface Enrollment {
  id: string;
  courseId: string;
  studentId: string;
  student: User;
  status: 'active' | 'completed' | 'dropped';
  progress: number;
  enrolledAt: string;
  completedAt?: string;
}

export interface Notification {
  id: string;
  userId: string;
  title: string;
  message: string;
  type: 'info' | 'success' | 'warning' | 'error';
  isRead: boolean;
  actionUrl?: string;
  createdAt: string;
}

// UI State types
export interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

export interface UIState {
  sidebarCollapsed: boolean;
  darkMode: boolean;
  notifications: Notification[];
}

// API Response types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  pagination: {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    itemsPerPage: number;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
  };
}

// Form types
export interface LoginForm {
  email: string;
  password: string;
}

export interface CourseForm {
  title: string;
  description: string;
  category: string;
  difficultyLevel: 'beginner' | 'intermediate' | 'advanced';
  durationHours: number;
  maxStudents?: number;
  enrollmentStartDate?: string;
  enrollmentEndDate?: string;
  courseStartDate?: string;
  courseEndDate?: string;
}

export interface ModuleForm {
  title: string;
  description?: string;
  type: 'lesson' | 'assignment' | 'quiz' | 'discussion';
}

export interface SubpageForm {
  title: string;
  description?: string;
}

export interface ContentForm {
  title: string;
  type: 'text' | 'video' | 'audio' | 'pdf' | 'image' | 'link';
  content: string;
}

export interface ExerciseForm {
  title: string;
  description?: string;
  type: 'multiple_choice' | 'true_false' | 'short_answer' | 'essay';
  maxAttempts?: number;
  timeLimit?: number;
}

export interface AssignmentForm {
  title: string;
  description: string;
  instructions: string;
  dueDate?: string;
  maxScore: number;
  submissionType: 'file' | 'text' | 'url';
  allowLateSubmission: boolean;
}

// Analytics types
export interface DashboardStats {
  totalCourses: number;
  totalStudents: number;
  totalTeachers: number;
  activeEnrollments: number;
  completionRate: number;
  averageGrade: number;
}

export interface CourseAnalytics {
  enrollmentTrend: Array<{ date: string; enrollments: number }>;
  completionRate: number;
  averageProgress: number;
  moduleEngagement: Array<{ moduleId: string; title: string; engagement: number }>;
  studentActivity: Array<{ date: string; activeStudents: number }>;
}

// Component prop types
export interface BaseComponentProps {
  className?: string;
  children?: React.ReactNode;
}

export interface ButtonProps extends BaseComponentProps {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
}

export interface ModalProps extends BaseComponentProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}

export interface TableColumn<T> {
  key: keyof T;
  label: string;
  sortable?: boolean;
  render?: (value: any, item: T) => React.ReactNode;
}

export interface TableProps<T> {
  data: T[];
  columns: TableColumn<T>[];
  loading?: boolean;
  onSort?: (key: keyof T, direction: 'asc' | 'desc') => void;
  onRowClick?: (item: T) => void;
}