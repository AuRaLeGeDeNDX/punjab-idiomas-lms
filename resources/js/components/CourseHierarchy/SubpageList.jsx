import React, { useState, useEffect } from 'react';
import SubpageItem from './SubpageItem';
import CreateSubpageModal from './CreateSubpageModal';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

const SubpageList = ({
    courseId,
    moduleId,
    subpages,
    userRole = 'teacher',
    onCreateSubpage,
    onUpdateSubpage,
    onDeleteSubpage,
    onReorderSubpages
}) => {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [draggedSubpages, setDraggedSubpages] = useState(subpages);

    // Update local state when subpages prop changes
    useEffect(() => {
        setDraggedSubpages(subpages);
    }, [subpages]);

    const handleDragEnd = (result) => {
        if (!result.destination) return;

        const items = Array.from(draggedSubpages);
        const [reorderedItem] = items.splice(result.source.index, 1);
        items.splice(result.destination.index, 0, reorderedItem);

        setDraggedSubpages(items);
        
        // Extract subpage IDs in new order
        const subpageIds = items.map(subpage => subpage.id);
        onReorderSubpages(moduleId, subpageIds);
    };

    const handleCreateSubpage = async (subpageData) => {
        const success = await onCreateSubpage(moduleId, subpageData);
        if (success) {
            setShowCreateModal(false);
        }
        return success;
    };

    return (
        <div className="subpage-list">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h6 className="mb-0 text-muted">
                    <i className="fas fa-file-alt me-1"></i>
                    Subpages ({subpages.length})
                </h6>
                <button
                    type="button"
                    className="btn btn-outline-primary btn-sm"
                    onClick={() => setShowCreateModal(true)}
                >
                    <i className="fas fa-plus me-1"></i>
                    Add Subpage
                </button>
            </div>

            {subpages.length === 0 ? (
                <div className="text-center py-4 border rounded bg-light">
                    <i className="fas fa-file-alt fa-2x text-muted mb-2"></i>
                    <p className="text-muted mb-2">No subpages in this module</p>
                    <button
                        type="button"
                        className="btn btn-sm btn-outline-primary"
                        onClick={() => setShowCreateModal(true)}
                    >
                        <i className="fas fa-plus me-1"></i>
                        Add First Subpage
                    </button>
                </div>
            ) : (
                <DragDropContext onDragEnd={handleDragEnd}>
                    <Droppable droppableId={`subpages-${moduleId}`}>
                        {(provided, snapshot) => (
                            <div
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                                className={`subpages-container ${
                                    snapshot.isDraggingOver ? 'drag-over' : ''
                                }`}
                            >
                                {draggedSubpages.map((subpage, index) => (
                                    <Draggable
                                        key={subpage.id}
                                        draggableId={`subpage-${subpage.id}`}
                                        index={index}
                                    >
                                        {(provided, snapshot) => (
                                            <div
                                                ref={provided.innerRef}
                                                {...provided.draggableProps}
                                                className={`subpage-drag-item ${
                                                    snapshot.isDragging ? 'dragging' : ''
                                                }`}
                                            >
                                                <SubpageItem
                                                    courseId={courseId}
                                                    moduleId={moduleId}
                                                    subpage={subpage}
                                                    userRole={userRole}
                                                    dragHandleProps={provided.dragHandleProps}
                                                    onUpdate={onUpdateSubpage}
                                                    onDelete={onDeleteSubpage}
                                                />
                                            </div>
                                        )}
                                    </Draggable>
                                ))}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </DragDropContext>
            )}

            <CreateSubpageModal
                show={showCreateModal}
                moduleId={moduleId}
                onClose={() => setShowCreateModal(false)}
                onSubmit={handleCreateSubpage}
            />
        </div>
    );
};

export default SubpageList;