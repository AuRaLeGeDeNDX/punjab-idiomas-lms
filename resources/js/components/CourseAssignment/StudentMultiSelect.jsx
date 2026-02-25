import React, { useState, useEffect, useMemo } from 'react';

const StudentMultiSelect = ({ 
    availableStudents = [], 
    selectedStudentIds = [], 
    onSelectionChange,
    loading = false,
    error = null,
    searchPlaceholder = "Search students...",
    showEnrollmentStatus = true 
}) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const studentsPerPage = 10;

    // Filter students based on search term
    const filteredStudents = useMemo(() => {
        if (!searchTerm.trim()) return availableStudents;
        
        const term = searchTerm.toLowerCase();
        return availableStudents.filter(student => 
            student.name.toLowerCase().includes(term) ||
            student.email.toLowerCase().includes(term)
        );
    }, [availableStudents, searchTerm]);

    // Paginate filtered students
    const paginatedStudents = useMemo(() => {
        const startIndex = (currentPage - 1) * studentsPerPage;
        return filteredStudents.slice(startIndex, startIndex + studentsPerPage);
    }, [filteredStudents, currentPage]);

    const totalPages = Math.ceil(filteredStudents.length / studentsPerPage);

    // Reset to first page when search changes
    useEffect(() => {
        setCurrentPage(1);
    }, [searchTerm]);

    const handleStudentToggle = (studentId) => {
        const newSelection = selectedStudentIds.includes(studentId)
            ? selectedStudentIds.filter(id => id !== studentId)
            : [...selectedStudentIds, studentId];
        
        onSelectionChange(newSelection);
    };

    const handleSelectAll = () => {
        const allCurrentPageIds = paginatedStudents.map(student => student.id);
        const allSelected = allCurrentPageIds.every(id => selectedStudentIds.includes(id));
        
        if (allSelected) {
            // Deselect all on current page
            const newSelection = selectedStudentIds.filter(id => !allCurrentPageIds.includes(id));
            onSelectionChange(newSelection);
        } else {
            // Select all on current page
            const newSelection = [...new Set([...selectedStudentIds, ...allCurrentPageIds])];
            onSelectionChange(newSelection);
        }
    };

    const isAllCurrentPageSelected = paginatedStudents.length > 0 && 
        paginatedStudents.every(student => selectedStudentIds.includes(student.id));

    if (loading) {
        return (
            <div className="d-flex align-items-center justify-content-center p-4">
                <div className="spinner-border text-primary me-2" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
                <span className="text-muted">Loading students...</span>
            </div>
        );
    }

    if (error) {
        return (
            <div className="alert alert-danger">
                <div className="d-flex">
                    <div className="flex-shrink-0">
                        <i className="fas fa-exclamation-triangle text-danger"></i>
                    </div>
                    <div className="ms-3">
                        <h6 className="alert-heading">Error loading students</h6>
                        <p className="mb-0">{error}</p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="mb-4">
            {/* Search Input */}
            <div className="input-group mb-3">
                <span className="input-group-text">
                    <i className="fas fa-search"></i>
                </span>
                <input
                    type="text"
                    className="form-control"
                    placeholder={searchPlaceholder}
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>

            {/* Selection Summary */}
            <div className="d-flex justify-content-between align-items-center mb-3">
                <small className="text-muted">
                    {selectedStudentIds.length} of {availableStudents.length} students selected
                </small>
                {filteredStudents.length !== availableStudents.length && (
                    <small className="text-muted">
                        Showing {filteredStudents.length} filtered results
                    </small>
                )}
            </div>

            {/* Select All for Current Page */}
            {paginatedStudents.length > 0 && (
                <div className="form-check mb-3">
                    <input
                        type="checkbox"
                        id="select-all-page"
                        checked={isAllCurrentPageSelected}
                        onChange={handleSelectAll}
                        className="form-check-input"
                    />
                    <label htmlFor="select-all-page" className="form-check-label">
                        Select all on this page
                    </label>
                </div>
            )}

            {/* Student List */}
            <div className="border rounded" style={{ maxHeight: '400px', overflowY: 'auto' }}>
                {paginatedStudents.length === 0 ? (
                    <div className="p-4 text-center text-muted">
                        {searchTerm ? 'No students found matching your search.' : 'No students available for assignment.'}
                    </div>
                ) : (
                    <div>
                        {paginatedStudents.map((student, index) => (
                            <div key={student.id} className={`p-3 ${index !== paginatedStudents.length - 1 ? 'border-bottom' : ''}`}>
                                <div className="form-check">
                                    <input
                                        type="checkbox"
                                        id={`student-${student.id}`}
                                        checked={selectedStudentIds.includes(student.id)}
                                        onChange={() => handleStudentToggle(student.id)}
                                        className="form-check-input"
                                    />
                                    <label 
                                        htmlFor={`student-${student.id}`}
                                        className="form-check-label w-100"
                                    >
                                        <div className="fw-medium">{student.name}</div>
                                        <div className="text-muted small">{student.email}</div>
                                        {showEnrollmentStatus && student.enrollment_status && (
                                            <div className="mt-1">
                                                <span className={`badge ${
                                                    student.enrollment_status === 'active' 
                                                        ? 'bg-success'
                                                        : student.enrollment_status === 'completed'
                                                        ? 'bg-primary'
                                                        : 'bg-secondary'
                                                }`}>
                                                    {student.enrollment_status}
                                                </span>
                                            </div>
                                        )}
                                    </label>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Pagination */}
            {totalPages > 1 && (
                <div className="d-flex justify-content-between align-items-center mt-3">
                    <small className="text-muted">
                        Showing {((currentPage - 1) * studentsPerPage) + 1} to {Math.min(currentPage * studentsPerPage, filteredStudents.length)} of {filteredStudents.length} results
                    </small>
                    <nav>
                        <ul className="pagination pagination-sm mb-0">
                            <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                                <button
                                    className="page-link"
                                    onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                                    disabled={currentPage === 1}
                                >
                                    Previous
                                </button>
                            </li>
                            <li className="page-item active">
                                <span className="page-link">
                                    {currentPage} of {totalPages}
                                </span>
                            </li>
                            <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                                <button
                                    className="page-link"
                                    onClick={() => setCurrentPage(prev => Math.min(totalPages, prev + 1))}
                                    disabled={currentPage === totalPages}
                                >
                                    Next
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            )}
        </div>
    );
};

export default StudentMultiSelect;