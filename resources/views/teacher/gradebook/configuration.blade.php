@extends('layouts.app')

@section('title', 'Grading Configuration - ' . $course->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Grading Configuration</h5>
                        <a href="{{ route('teacher.gradebook.index', $course) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Grade Book
                        </a>
                    </div>
                    <p class="text-muted mb-0 mt-2">{{ $course->title }}</p>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('teacher.gradebook.update-configuration', $course) }}">
                        @csrf
                        @method('PUT')

                        <!-- Grading Scheme -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Grading Scheme Weights</label>
                            <p class="text-muted small">Configure how different assignment types contribute to the final grade. Total must equal 100%.</p>
                            
                            @php
                                $currentScheme = $course->getGradingScheme();
                                $assignmentTypes = ['quiz' => 'Quizzes', 'assignment' => 'Assignments', 'project' => 'Projects', 'exam' => 'Exams'];
                            @endphp

                            <div class="row">
                                @foreach($assignmentTypes as $type => $label)
                                    <div class="col-md-6 mb-3">
                                        <label for="grading_scheme_{{ $type }}" class="form-label">{{ $label }}</label>
                                        <div class="input-group">
                                            <input type="number" 
                                                   class="form-control @error('grading_scheme.' . $type) is-invalid @enderror" 
                                                   id="grading_scheme_{{ $type }}"
                                                   name="grading_scheme[{{ $type }}]" 
                                                   value="{{ old('grading_scheme.' . $type, $currentScheme[$type] ?? 0) }}"
                                                   min="0" 
                                                   max="100" 
                                                   step="1">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('grading_scheme.' . $type)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Total Weight:</strong> <span id="total-weight">0</span>%
                                <span id="weight-status" class="ms-2"></span>
                            </div>

                            @error('grading_scheme')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Passing Grade -->
                        <div class="mb-4">
                            <label for="passing_grade" class="form-label fw-bold">Passing Grade Threshold</label>
                            <p class="text-muted small">Minimum percentage required to pass the course.</p>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" 
                                       class="form-control @error('passing_grade') is-invalid @enderror" 
                                       id="passing_grade"
                                       name="passing_grade" 
                                       value="{{ old('passing_grade', $course->passing_grade) }}"
                                       min="0" 
                                       max="100" 
                                       step="0.01">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('passing_grade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Auto-publish Grades -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="auto_publish_grades"
                                       name="auto_publish_grades" 
                                       value="1"
                                       {{ old('auto_publish_grades', $course->auto_publish_grades) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="auto_publish_grades">
                                    Auto-publish Grades
                                </label>
                            </div>
                            <p class="text-muted small ms-4">Automatically publish grades to students when they are entered.</p>
                        </div>

                        <!-- Letter Grade Scale -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Letter Grade Scale</label>
                            <p class="text-muted small">Standard letter grade conversion (not configurable).</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" style="max-width: 400px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Letter Grade</th>
                                            <th>Percentage Range</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>A</td><td>90% - 100%</td></tr>
                                        <tr><td>B</td><td>80% - 89%</td></tr>
                                        <tr><td>C</td><td>70% - 79%</td></tr>
                                        <tr><td>D</td><td>60% - 69%</td></tr>
                                        <tr><td>F</td><td>0% - 59%</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('teacher.gradebook.index', $course) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="save-button" disabled>
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const weightInputs = document.querySelectorAll('input[name^="grading_scheme"]');
    const totalWeightSpan = document.getElementById('total-weight');
    const weightStatus = document.getElementById('weight-status');
    const saveButton = document.getElementById('save-button');

    function updateTotalWeight() {
        let total = 0;
        weightInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        totalWeightSpan.textContent = total;

        if (total === 100) {
            weightStatus.innerHTML = '<span class="badge bg-success">✓ Valid</span>';
            saveButton.disabled = false;
        } else {
            weightStatus.innerHTML = '<span class="badge bg-danger">✗ Must equal 100%</span>';
            saveButton.disabled = true;
        }
    }

    weightInputs.forEach(input => {
        input.addEventListener('input', updateTotalWeight);
    });

    // Initial calculation
    updateTotalWeight();
});
</script>
@endsection