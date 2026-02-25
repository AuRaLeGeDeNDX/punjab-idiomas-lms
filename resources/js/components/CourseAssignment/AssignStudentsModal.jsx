import React, { useState, useEffect } from 'react';
import StudentMultiSelect from './StudentMultiSelect';
import EnrollmentList from './EnrollmentList';
import Modal from '../Common/Modal';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import SuccessAlert from '../Common/SuccessAlert';

const AssignStudentsModal = ({
    show,
    onClose,
    course,
    onAssignmentComplete
}) => {
    const [availableStudents, setAvailableStudents] = useState([]);
    const [enrolledStudents, setEnrolledStudents] = useState([]);
    const [selectedStudentIds, setSelectedStudentIds] = useState([]);
    const [assignmentNotes, setAssignmentNotes] = useState('');
    const [loading, setLoading] = useState(false);
    const [loadingStudents, setLoadingStudents] = useState(false);
    const [loadingEnrolled, setLoadingEnrolled] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [activeTab, setActiveTab] = useState('assign'); // 'assign' or 'enrolled'

    // Load data when modal opens
    useEffect(() => {
        if (show && course) {
            loadAvailableStudents();
            loadEnrolledStudents();
        }
    }, [show, course]);

    // Reset form when modal closes
    useEffect(() => {
        if (!show) {
            setSelectedStudentIds([]);
            setAssignmentNotes('');
            setError(null);
            setSuccess(null);
            setActiveTab('assign');
        }
    }, [show]);

    const loadAvailableStudents = async () => {
        setLoadingStudents(true);
        setError(null);

        try {
            const response = await fetch(`/api/courses/${course.id}/available-students?t=${new Date().getTime()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to load available students');
            }

            const data = await response.json();
            setAvailableStudents(data.data.available_students || []);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoadingStudents(false);
        }
    };

    const loadEnrolledStudents = async () => {
        setLoadingEnrolled(true);

        try {
            const response = await fetch(`/api/courses/${course.id}/enrolled-students?t=${new Date().getTime()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to load enrolled students');
            }

            const data = await response.json();
            setEnrolledStudents(data.data.enrolled_students || []);
        } catch (err) {
            console.error('Failed to load enrolled students:', err);
            setEnrolledStudents([]);
        } finally {
            setLoadingEnrolled(false);
        }
    };

    const handleAssignStudents = async () => {
        if (selectedStudentIds.length === 0) {
            setError('Please select at least one student to assign.');
            return;
        }

        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            const response = await fetch(`/api/courses/${course.id}/assign-students`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    student_ids: selectedStudentIds,
                    notes: assignmentNotes.trim() || null,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to assign students');
            }

            // Show success message with summary
            const { successful_count, failed_count } = data.summary;
            let successMessage = `Successfully assigned ${successful_count} student${successful_count !== 1 ? 's' : ''} to ${course.title}`;

            if (failed_count > 0) {
                successMessage += `. ${failed_count} assignment${failed_count !== 1 ? 's' : ''} failed.`;
            }

            setSuccess(successMessage);

            // Show detailed results if there were failures
            if (data.data.failed.length > 0) {
                console.warn('Assignment failures:', data.data.failed);
            }

            // Reset form
            setSelectedStudentIds([]);
            setAssignmentNotes('');

            // Reload data to reflect changes
            await Promise.all([loadAvailableStudents(), loadEnrolledStudents()]);

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

    const handleRemoveStudent = async (enrollment) => {
        try {
            const response = await fetch(`/api/courses/${course.id}/students/${enrollment.student.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to remove student');
            }

            setSuccess(`Successfully removed ${enrollment.student.name} from ${course.title}`);

            // Reload data to reflect changes
            await Promise.all([loadAvailableStudents(), loadEnrolledStudents()]);

            // Notify parent component
            if (onAssignmentComplete) {
                onAssignmentComplete({ type: 'removal', data });
            }

        } catch (err) {
            setError(err.message);
            throw err; // Re-throw to let EnrollmentList handle the error state
        }
    };

    const handleClose = () => {
        if (!loading) {
            onClose();
        }
    };

    // Check if current user can remove students (this should come from backend/props in real implementation)
    const canRemoveStudents = true; // For now, assume they can if they can assign

    return (
        <Modal
            show={show}
            onClose={handleClose}
            title={`Manage Students - ${course?.title || 'Course'}`}
            size="lg"
        >
            <div className="modal-body">
                {/* Course Information */}
                {course && (
                    <div className="alert alert-info mb-4">
                        <h5 className="alert-heading">{course.title}</h5>
                        {course.description && (
                            <p className="mb-2">{course.description}</p>
                        )}
                        <div className="small">
                            <span><strong>Teacher:</strong> {course.teacher_name}</span>
                            {course.max_students && (
                                <span className="ms-3">
                                    <strong>Capacity:</strong> {enrolledStudents.length} / {course.max_students}
                                </span>
                            )}
                        </div>
                    </div>
                )}

                {/* Error Alert */}
                {error && (
                    <ErrorAlert
                        message={error}
                        onClose={() => setError(null)}
                    />
                )}

                {/* Success Alert */}
                {success && (
                    <SuccessAlert
                        message={success}
                        onClose={() => setSuccess(null)}
                    />
                )}

                {/* Tabs */}
                <ul className="nav nav-tabs mb-4">
                    <li className="nav-item">
                        <button
                            className={`nav-link ${activeTab === 'assign' ? 'active' : ''}`}
                            onClick={() => setActiveTab('assign')}
                        >
                            <i className="fas fa-user-plus me-2"></i>
                            Assign Students
                        </button>
                    </li>
                    <li className="nav-item">
                        <button
                            className={`nav-link ${activeTab === 'enrolled' ? 'active' : ''}`}
                            onClick={() => setActiveTab('enrolled')}
                        >
                            <i className="fas fa-users me-2"></i>
                            Enrolled Students
                            {enrolledStudents.length > 0 && (
                                <span className="badge bg-primary ms-2">{enrolledStudents.length}</span>
                            )}
                        </button>
                    </li>
                </ul>

                {/* Tab Content */}
                {activeTab === 'assign' && (
                    <>
                        {/* Student Selection */}
                        <div className="mb-4">
                            <label className="form-label fw-medium">
                                Select Students to Assign
                            </label>
                            <StudentMultiSelect
                                availableStudents={availableStudents}
                                selectedStudentIds={selectedStudentIds}
                                onSelectionChange={setSelectedStudentIds}
                                loading={loadingStudents}
                                error={loadingStudents ? null : (availableStudents.length === 0 ? 'No students available for assignment' : null)}
                                searchPlaceholder="Search students by name or email..."
                                showEnrollmentStatus={false}
                            />
                        </div>

                        {/* Assignment Notes */}
                        <div className="mb-4">
                            <label htmlFor="assignment-notes" className="form-label fw-medium">
                                Assignment Notes (Optional)
                            </label>
                            <textarea
                                id="assignment-notes"
                                rows={3}
                                className="form-control"
                                placeholder="Add any notes about this assignment..."
                                value={assignmentNotes}
                                onChange={(e) => setAssignmentNotes(e.target.value)}
                                maxLength={1000}
                            />
                            <div className="form-text">
                                {assignmentNotes.length}/1000 characters
                            </div>
                        </div>
                    </>
                )}

                {activeTab === 'enrolled' && (
                    <EnrollmentList
                        enrolledStudents={enrolledStudents}
                        loading={loadingEnrolled}
                        error={null}
                        onRemoveStudent={handleRemoveStudent}
                        canRemoveStudents={canRemoveStudents}
                    />
                )}
            </div>

            <div className="modal-footer">
                <button
                    type="button"
                    onClick={handleClose}
                    disabled={loading}
                    className="btn btn-secondary"
                >
                    {activeTab === 'enrolled' ? 'Close' : 'Cancel'}
                </button>
                {activeTab === 'assign' && (
                    <button
                        type="button"
                        onClick={handleAssignStudents}
                        disabled={loading || selectedStudentIds.length === 0}
                        className="btn btn-primary d-flex align-items-center"
                    >
                        {loading && <LoadingSpinner size="sm" />}
                        Assign {selectedStudentIds.length > 0 ? `${selectedStudentIds.length} ` : ''}Student{selectedStudentIds.length !== 1 ? 's' : ''}
                    </button>
                )}
            </div>
        </Modal>
    );
};

export default AssignStudentsModal;