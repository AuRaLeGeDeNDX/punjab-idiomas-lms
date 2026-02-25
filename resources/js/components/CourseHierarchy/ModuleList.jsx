import React, { useState, useEffect } from 'react';
import ModuleItem from './ModuleItem';
import CreateModuleModal from './CreateModuleModal';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

const ModuleList = ({
    courseId,
    modules,
    userRole = 'teacher',
    onCreateModule,
    onUpdateModule,
    onDeleteModule,
    onReorderModules,
    onCreateSubpage,
    onUpdateSubpage,
    onDeleteSubpage,
    onReorderSubpages
}) => {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [draggedModules, setDraggedModules] = useState(modules);

    // Update local state when modules prop changes
    useEffect(() => {
        setDraggedModules(modules);
    }, [modules]);

    const handleDragEnd = (result) => {
        if (!result.destination) return;

        const items = Array.from(draggedModules);
        const [reorderedItem] = items.splice(result.source.index, 1);
        items.splice(result.destination.index, 0, reorderedItem);

        setDraggedModules(items);
        
        // Extract module IDs in new order
        const moduleIds = items.map(module => module.id);
        onReorderModules(moduleIds);
    };

    const handleCreateModule = async (moduleData) => {
        const success = await onCreateModule(moduleData);
        if (success) {
            setShowCreateModal(false);
        }
        return success;
    };

    return (
        <div className="module-list">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h6 className="mb-0">
                    Modules ({modules.length})
                </h6>
                <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    onClick={() => setShowCreateModal(true)}
                >
                    <i className="fas fa-plus me-1"></i>
                    Add Module
                </button>
            </div>

            {modules.length === 0 ? (
                <div className="text-center py-5">
                    <i className="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <h6 className="text-muted">No modules yet</h6>
                    <p className="text-muted mb-3">
                        Start building your course by adding modules to organize your content.
                    </p>
                    <button
                        type="button"
                        className="btn btn-outline-primary"
                        onClick={() => setShowCreateModal(true)}
                    >
                        <i className="fas fa-plus me-1"></i>
                        Create First Module
                    </button>
                </div>
            ) : (
                <DragDropContext onDragEnd={handleDragEnd}>
                    <Droppable droppableId="modules">
                        {(provided, snapshot) => (
                            <div
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                                className={`modules-container ${
                                    snapshot.isDraggingOver ? 'drag-over' : ''
                                }`}
                            >
                                {draggedModules.map((module, index) => (
                                    <Draggable
                                        key={module.id}
                                        draggableId={module.id.toString()}
                                        index={index}
                                    >
                                        {(provided, snapshot) => (
                                            <div
                                                ref={provided.innerRef}
                                                {...provided.draggableProps}
                                                className={`module-drag-item ${
                                                    snapshot.isDragging ? 'dragging' : ''
                                                }`}
                                            >
                                                <ModuleItem
                                                    courseId={courseId}
                                                    module={module}
                                                    userRole={userRole}
                                                    dragHandleProps={provided.dragHandleProps}
                                                    onUpdate={onUpdateModule}
                                                    onDelete={onDeleteModule}
                                                    onCreateSubpage={onCreateSubpage}
                                                    onUpdateSubpage={onUpdateSubpage}
                                                    onDeleteSubpage={onDeleteSubpage}
                                                    onReorderSubpages={onReorderSubpages}
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

            <CreateModuleModal
                show={showCreateModal}
                onClose={() => setShowCreateModal(false)}
                onSubmit={handleCreateModule}
            />
        </div>
    );
};

export default ModuleList;