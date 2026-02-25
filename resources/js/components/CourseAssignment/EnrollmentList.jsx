import React, { useState } from 'react';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';

const EnrollmentList = ({ 
    enrolledStudents, 
    loading, 
    error, 
    onRemoveStudent, 
    canRemoveStudents = false 
}) => {
    const [removingStudentId, setRemovingStudentId] = useState(null);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [studentToRemove, setStudentToRemove] = useState(null);

    const handleRemoveClick = (student) => {
        setStudentToRemove(student);
        setShowConfirmDialog(true);
    };

    const handleConfirmRemove = async () => {
        if (!studentToRemove) return;

        setRemovingStudentId(studentToRemove.student.id);
        setShowConfirmDialog(false);

        try {
            await onRemoveStudent(studentToRemove);
        } catch (error) {
            console.error('Failed to remove student:', error);
        } finally {
            setRemovingStudentId(null);
            setStudentToRemove(null);
        }
    };

    const handleCancelRemove = () => {
        setShowConfirmDialog(false);
        setStudentToRemove(null);
    };

    if (loading) {
        return (
            <div className="d-flex justify-content-center py-4">
                <LoadingSpinner />
                <span className="ms-2">Loading enrolled students...</span>
            </div>
        );
    }

    if (error) {
        return <ErrorAlert message={error} />;
    }

    if (!enrolledStudents || enrolledStudents.length === 0) {
        return (
            <div className="text-center py-4">
                <i className="fas fa-users fa-2x text-muted mb-3"></i>
                <h6 className="text-muted">No students enrolled</h6>
                <p className="text-muted mb-0">This course doesn't have any enrolled students yet.</p>
            </div>
        );
    }

    return (
        <>
            <div className="enrolled-students-list">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h6 className="mb-0">
                        Enrolled Students ({enrolledStudents.length})
                    </h6>
                </div>

                <div className="list-group">
                    {enrolledStudents.map((enrollment) => (
                        <div key={enrollment.enrollment_id} className="list-group-item">
                            <div className="d-flex justify-content-between align-items-start">
                                <div className="flex-grow-1">
                                    <div className="d-flex align-items-center mb-2">
                                        <div className="me-3">
                                            <div className="fw-medium">{enrollment.student.name}</div>
                                            <small className="text-muted">{enrollment.student.email}</small>
                                        </div>
                                        {enrollment.progress_percentage > 0 && (
                                            <div className="badge bg-info">
                                                {Math.round(enrollment.progress_percentage)}% Complete
                                            </div>
                                        )}
                                    </div>
                                    
                                    <div className="row text-muted small">
                                        <div className="col-md-6">
                                            <i className="fas fa-calendar-alt me-1"></i>
                                            Enrolled: {new Date(enrollment.enrolled_at).toLocaleDateString()}
                                        </div>
                                        {enrollment.was_assigned && enrollment.assignor && (
                                            <div className="col-md-6">
                                                <i className="fas fa-user-check me-1"></i>
                                                Assigned by: {enrollment.assignor.name}
                                            </div>
                                        )}
                                    </div>

                                    {enrollment.assignment_notes && (
                                        <div className="mt-2">
                                            <small className="text-muted">
                                                <i className="fas fa-sticky-note me-1"></i>
                                                {enrollment.assignment_notes}
                                            </small>
                                        </div>
                                    )}
                                </div>

                                {canRemoveStudents && (
                                    <div className="ms-3">
                                        <button
                                            type="button"
                                            onClick={() => handleRemoveClick(enrollment)}
                                            disabled={removingStudentId === enrollment.student.id}
                                            className="btn btn-sm btn-outline-danger"
                                            title="Remove student from course"
                                        >
                                            {removingStudentId === enrollment.student.id ? (
                                                <LoadingSpinner size="sm" />
                                            ) : (
                                                <i className="fas fa-user-minus"></i>
                                            )}
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Confirmation Dialog */}
            {showConfirmDialog && studentToRemove && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Confirm Student Removal</h5>
                                <button
                                    type="button"
                                    className="btn-close"
                                    onClick={handleCancelRemove}
                                ></button>
                            </div>
                            <div className="modal-body">
                                <div className="alert alert-warning">
                                    <i className="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> This action will remove the student from the course.
                                </div>
                                
                                <p>
                                    Are you sure you want to remove <strong>{studentToRemove.student.name}</strong> from this course?
                                </p>

                                <div className="bg-light p-3 rounded">
                                    <h6>Impact of removal:</h6>
                                    <ul className="mb-0">
                                        <li>Student will lose access to course materials</li>
                                        <li>Progress data will be preserved but course will be marked as "dropped"</li>
                                        <li>Student will need to be re-assigned to access the course again</li>
                                        {studentToRemove.progress_percentage > 0 && (
                                            <li className="text-warning">
                                                <strong>Current progress: {Math.round(studentToRemove.progress_percentage)}%</strong>
                                            </li>
                                        )}
                                    </ul>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    onClick={handleCancelRemove}
                                    className="btn btn-secondary"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onClick={handleConfirmRemove}
                                    className="btn btn-danger"
                                >
                                    <i className="fas fa-user-minus me-2"></i>
                                    Remove Student
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};

export default EnrollmentList;