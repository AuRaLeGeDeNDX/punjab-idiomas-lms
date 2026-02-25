import React, { useState, useEffect } from 'react';
import Modal from '../Common/Modal';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import SuccessAlert from '../Common/SuccessAlert';

const AssignCourseModal = ({ 
    isOpen, 
    onClose, 
    student,
    onAssignmentComplete 
}) => {
    const [availableCourses, setAvailableCourses] = useState([]);
    const [selectedCourseId, setSelectedCourseId] = useState('');
    const [assignmentNotes, setAssignmentNotes] = useState('');
    const [loading, setLoading] = useState(false);
    const [loadingCourses, setLoadingCourses] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [studentCourses, setStudentCourses] = useState([]);

    // Load available courses and student's current courses when modal opens
    useEffect(() => {
        if (isOpen && student) {
            loadStudentCourses();
            loadAvailableCourses();
        }
    }, [isOpen, student]);

    // Reset form when modal closes
    useEffect(() => {
        if (!isOpen) {
            setSelectedCourseId('');
            setAssignmentNotes('');
            setError(null);
            setSuccess(null);
        }
    }, [isOpen]);

    const loadStudentCourses = async () => {
        try {
            const response = await fetch(`/api/students/${student.id}/courses`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                setStudentCourses(data.data.courses || []);
            }
        } catch (err) {
            console.warn('Failed to load student courses:', err);
        }
    };

    const loadAvailableCourses = async () => {
        setLoadingCourses(true);
        setError(null);

        try {
            // For now, we'll fetch all courses and filter client-side
            // In a real implementation, you might want a dedicated endpoint
            const response = await fetch('/api/courses', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load available courses');
            }

            const data = await response.json();
            // Filter out courses the student is already enrolled in
            const enrolledCourseIds = studentCourses.map(course => course.course.id);
            const available = (data.courses || data.data || []).filter(course => 
                !enrolledCourseIds.includes(course.id) && course.is_published
            );
            
            setAvailableCourses(available);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoadingCourses(false);
        }
    };

    const handleAssignCourse = async () => {
        if (!selectedCourseId) {
            setError('Please select a course to assign.');
            return;
        }

        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const response = await fetch(`/api/courses/${selectedCourseId}/assign-students`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    student_ids: [student.id],
                    notes: assignmentNotes.trim() || null,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to assign course');
            }

            const selectedCourse = availableCourses.find(course => course.id == selectedCourseId);
            setSuccess(`Successfully assigned ${student.name} to ${selectedCourse?.title || 'the course'}`);

            // Reset form
            setSelectedCourseId('');
            setAssignmentNotes('');

            // Reload data
            await loadStudentCourses();
            await loadAvailableCourses();

            // Notify parent component
            if (onAssignmentComplete) {
                onAssignmentComplete(data);
            }

        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        if (!loading) {
            onClose();
        }
    };

    const selectedCourse = availableCourses.find(course => course.id == selectedCourseId);

    return (
        <Modal
            isOpen={isOpen}
            onClose={handleClose}
            title={`Assign Course to ${student?.name || 'Student'}`}
            size="lg"
        >
            <div className="space-y-6">
                {/* Student Information */}
                {student && (
                    <div className="bg-green-50 border border-green-200 rounded-md p-4">
                        <h3 className="text-lg font-medium text-green-900">{student.name}</h3>
                        <p className="mt-1 text-sm text-green-700">{student.email}</p>
                        <div className="mt-2 text-sm text-green-600">
                            Currently enrolled in {studentCourses.length} course{studentCourses.length !== 1 ? 's' : ''}
                        </div>
                    </div>
                )}

                {/* Current Enrollments */}
                {studentCourses.length > 0 && (
                    <div>
                        <h4 className="text-sm font-medium text-gray-700 mb-2">Current Enrollments</h4>
                        <div className="bg-gray-50 border border-gray-200 rounded-md p-3 max-h-32 overflow-y-auto">
                            <div className="space-y-1">
                                {studentCourses.map((enrollment) => (
                                    <div key={enrollment.enrollment_id} className="flex items-center justify-between text-sm">
                                        <span className="text-gray-900">{enrollment.course.title}</span>
                                        <span className={`px-2 py-0.5 rounded text-xs font-medium ${
                                            enrollment.status === 'active' 
                                                ? 'bg-green-100 text-green-800'
                                                : enrollment.status === 'completed'
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-gray-100 text-gray-800'
                                        }`}>
                                            {enrollment.status}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* Error Alert */}
                {error && (
                    <ErrorAlert 
                        message={error} 
                        onDismiss={() => setError(null)} 
                    />
                )}

                {/* Success Alert */}
                {success && (
                    <SuccessAlert 
                        message={success} 
                        onDismiss={() => setSuccess(null)} 
                    />
                )}

                {/* Course Selection */}
                <div>
                    <label htmlFor="course-select" className="block text-sm font-medium text-gray-700 mb-2">
                        Select Course to Assign
                    </label>
                    {loadingCourses ? (
                        <div className="flex items-center justify-center p-4 border border-gray-300 rounded-md">
                            <LoadingSpinner size="sm" className="mr-2" />
                            <span className="text-gray-600">Loading courses...</span>
                        </div>
                    ) : (
                        <select
                            id="course-select"
                            value={selectedCourseId}
                            onChange={(e) => setSelectedCourseId(e.target.value)}
                            className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select a course...</option>
                            {availableCourses.map((course) => (
                                <option key={course.id} value={course.id}>
                                    {course.title} - {course.teacher_name || 'No teacher assigned'}
                                </option>
                            ))}
                        </select>
                    )}
                    
                    {!loadingCourses && availableCourses.length === 0 && (
                        <p className="mt-2 text-sm text-gray-500">
                            No courses available for assignment. The student may already be enrolled in all available courses.
                        </p>
                    )}
                </div>

                {/* Selected Course Details */}
                {selectedCourse && (
                    <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h4 className="text-sm font-medium text-blue-900">Course Details</h4>
                        <div className="mt-2 text-sm text-blue-700">
                            <p><strong>Title:</strong> {selectedCourse.title}</p>
                            {selectedCourse.description && (
                                <p><strong>Description:</strong> {selectedCourse.description}</p>
                            )}
                            <p><strong>Teacher:</strong> {selectedCourse.teacher_name || 'Not assigned'}</p>
                            {selectedCourse.max_students && (
                                <p><strong>Capacity:</strong> {selectedCourse.current_enrollment_count || 0} / {selectedCourse.max_students}</p>
                            )}
                        </div>
                    </div>
                )}

                {/* Assignment Notes */}
                <div>
                    <label htmlFor="assignment-notes" className="block text-sm font-medium text-gray-700 mb-2">
                        Assignment Notes (Optional)
                    </label>
                    <textarea
                        id="assignment-notes"
                        rows={3}
                        className="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add any notes about this assignment..."
                        value={assignmentNotes}
                        onChange={(e) => setAssignmentNotes(e.target.value)}
                        maxLength={1000}
                    />
                    <p className="mt-1 text-sm text-gray-500">
                        {assignmentNotes.length}/1000 characters
                    </p>
                </div>

                {/* Action Buttons */}
                <div className="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        onClick={handleClose}
                        disabled={loading}
                        className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onClick={handleAssignCourse}
                        disabled={loading || !selectedCourseId}
                        className="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    >
                        {loading && <LoadingSpinner size="sm" className="mr-2" />}
                        Assign Course
                    </button>
                </div>
            </div>
        </Modal>
    );
};

export default AssignCourseModal;