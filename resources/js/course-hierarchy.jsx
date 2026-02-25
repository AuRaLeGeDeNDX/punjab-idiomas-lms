import React from 'react';
import { createRoot } from 'react-dom/client';
import CourseEditPage from './components/CourseHierarchy/CourseEditPage';

console.log('%c[CourseHierarchy] File loaded successfully!', 'color: green; font-weight: bold; font-size: 14px;');

// Initialize course hierarchy components
document.addEventListener('DOMContentLoaded', function() {
    console.log('[CourseHierarchy] DOM loaded, looking for course-hierarchy-app element...');
    
    const courseHierarchyElement = document.getElementById('course-hierarchy-app');
    
    if (courseHierarchyElement) {
        console.log('[CourseHierarchy] Element found!', courseHierarchyElement);
        
        const courseId = courseHierarchyElement.dataset.courseId;
        const csrfToken = courseHierarchyElement.dataset.csrfToken;
        const userRole = courseHierarchyElement.dataset.userRole || 'teacher';
        
        console.log('[CourseHierarchy] Initializing with:', { courseId, userRole });
        
        try {
            const root = createRoot(courseHierarchyElement);
            root.render(
                <CourseEditPage 
                    courseId={courseId} 
                    csrfToken={csrfToken}
                    userRole={userRole}
                />
            );
            console.log('%c[CourseHierarchy] Component rendered successfully!', 'color: green; font-weight: bold;');
        } catch (error) {
            console.error('[CourseHierarchy] Error rendering component:', error);
        }
    } else {
        console.error('%c[CourseHierarchy] Element NOT found - component will not render', 'color: red; font-weight: bold;');
    }
});
