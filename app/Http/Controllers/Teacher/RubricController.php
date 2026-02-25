<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Rubric;
use App\Models\RubricCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RubricController extends Controller
{
    /**
     * Show the form for creating a rubric.
     */
    public function create(Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        return view('teacher.rubrics.create', compact('assignment'));
    }

    /**
     * Store a newly created rubric.
     */
    public function store(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'criteria' => 'required|array|min:1',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.max_points' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $rubric = Rubric::create([
                'assignment_id' => $assignment->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            foreach ($validated['criteria'] as $index => $criterion) {
                RubricCriterion::create([
                    'rubric_id' => $rubric->id,
                    'criterion_name' => $criterion['name'],
                    'criterion_description' => $criterion['description'] ?? null,
                    'max_points' => $criterion['max_points'],
                    'order_index' => $index,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('teacher.courses.modules.subpages.assignments.show', [
                    $assignment->course,
                    $assignment->module,
                    $assignment->subpage,
                    $assignment
                ])
                ->with('success', 'Rubric created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create rubric: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the rubric.
     */
    public function edit(Assignment $assignment, Rubric $rubric)
    {
        Gate::authorize('update', $assignment);

        if ($rubric->assignment_id !== $assignment->id) {
            abort(404);
        }

        $rubric->load('criteria');

        return view('teacher.rubrics.edit', compact('assignment', 'rubric'));
    }

    /**
     * Update the specified rubric.
     */
    public function update(Request $request, Assignment $assignment, Rubric $rubric)
    {
        Gate::authorize('update', $assignment);

        if ($rubric->assignment_id !== $assignment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'criteria' => 'required|array|min:1',
            'criteria.*.id' => 'nullable|exists:rubric_criteria,id',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string',
            'criteria.*.max_points' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $rubric->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            // Delete removed criteria
            $keepIds = collect($validated['criteria'])->pluck('id')->filter();
            $rubric->criteria()->whereNotIn('id', $keepIds)->delete();

            // Update or create criteria
            foreach ($validated['criteria'] as $index => $criterion) {
                if (isset($criterion['id'])) {
                    RubricCriterion::where('id', $criterion['id'])->update([
                        'criterion_name' => $criterion['name'],
                        'criterion_description' => $criterion['description'] ?? null,
                        'max_points' => $criterion['max_points'],
                        'order_index' => $index,
                    ]);
                } else {
                    RubricCriterion::create([
                        'rubric_id' => $rubric->id,
                        'criterion_name' => $criterion['name'],
                        'criterion_description' => $criterion['description'] ?? null,
                        'max_points' => $criterion['max_points'],
                        'order_index' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('teacher.courses.modules.subpages.assignments.show', [
                    $assignment->course,
                    $assignment->module,
                    $assignment->subpage,
                    $assignment
                ])
                ->with('success', 'Rubric updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update rubric: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified rubric.
     */
    public function destroy(Assignment $assignment, Rubric $rubric)
    {
        Gate::authorize('update', $assignment);

        if ($rubric->assignment_id !== $assignment->id) {
            abort(404);
        }

        $rubric->delete();

        return redirect()
            ->route('teacher.courses.modules.subpages.assignments.show', [
                $assignment->course,
                $assignment->module,
                $assignment->subpage,
                $assignment
            ])
            ->with('success', 'Rubric deleted successfully.');
    }
}
