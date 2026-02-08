import { v4 as uuidv4 } from 'uuid';
import { defineStore } from 'pinia';
import { Smartphone, GitBranch } from 'lucide-vue-next';
import { useNotificationStore as notificationState } from '@Stores/notification-store.js';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

const CANVAS_ACTIONS = [
    { value: 'interactive_screen', label: 'Interactive Screen', description: 'Show messages and collect user responses.', icon: Smartphone },
    { value: 'decision_point', label: 'Decision Point', description: 'Direct users to different paths based on specific rules or information.', icon: GitBranch },
];

const INITIAL_FEATURES = [
    {
        label: 'Content',
        value: 'content',
        subOptions: [
            { label: 'Basic', value: 'basic content', description: 'Simple text with support for variables like {{ firstName }}' },
            { label: 'Code Editor', value: 'code content', description: 'Write PHP, Python or JavaScript code for dynamic content' },
        ],
    },
    { label: 'Enter Input', value: 'input' },
    { label: 'Select Option', value: 'select' },
];

const EVENT_TYPES = [
    { value: 'rest api', label: 'REST API', description: 'Fetch or send data to an external server', icon: 'Globe' },
    { value: 'soap api', label: 'SOAP API', description: 'Integrate with legacy XML web services', icon: 'FileCode' },
];

const INPUT_VALIDATION_RULE_OPTIONS = [
    { label: 'Only Letters', value: 'alpha' },
    { label: 'Only Numbers', value: 'numeric' },
    { label: 'Only Letters And Numbers', value: 'alphanumeric' },
    { label: 'Minimum Character Length', value: 'min_length' },
    { label: 'Maximum Character Length', value: 'max_length' },
    { label: 'Equal To Character Length', value: 'equal_length' },
    { label: 'Equal To (=)', value: 'eq' },
    { label: 'Not Equal To', value: 'neq' },
    { label: 'Less Than (<)', value: 'lt' },
    { label: 'Less Than Or Equal (<=)', value: 'lte' },
    { label: 'Greater Than (>)', value: 'gt' },
    { label: 'Greater Than Or Equal (>=)', value: 'gte' },
    { label: 'In Between (Including Inputs)', value: 'between_inclusive' },
    { label: 'In Between (Excluding Inputs)', value: 'between_exclusive' },
    { label: 'Validate Email', value: 'email' },
    { label: 'Validate Money', value: 'money' },
    { label: 'Validate Date Format (DDMMYYYY)', value: 'date_ddmmyyyy' },
    { label: 'Validate Date Format (DD/MM/YYYY)', value: 'date_dd_mm_yyyy' },
    { label: 'Validate Date Format (DD-MM-YYYY)', value: 'date_dd_mm_yyyy_dash' },
    { label: 'No Spaces', value: 'no_spaces' },
    { label: 'Custom Regex', value: 'regex' },
];

export const useVersionStore = defineStore('version', {
    state: () => ({
        version: null,
        versionForm: null,
        isLoadingVersion: false,
        isCreatingVersion: false,
        isUpdatingVersion: false,
        isDeletingVersion: false,

        currentStepId: null,
        vueFlowInstance: null,
        currentFeatureId: null,
        initialStepHistory: [],

        simulatorPathNodes: [],
        simulatorPathEdges: [],
        simulatorPathLogic: {},
        simulatorStepResults: {},
        simulatorTerminalStepId: null,

        featuresModal: null,
        stepEditModal: null,
        eventPickerModal: null,
        inputFeatureModal: null,
        soapApiEventModal: null,
        restApiEventModal: null,
        decisionPointModal: null,
        canvasActionsModal: null,
        deleteVersionModal: null,
        versionDetailsModal: null,
        codeContentEditorModal: null,
        basicContentEditorModal: null,
    }),
    getters: {
        builder() {
            return this.versionForm?.builder;
        },
        steps() {
            return this.builder?.steps;
        },
        features() {
            return this.builder?.features;
        },
        initialStepId() {
            return this.builder?.initial_step_id;
        },
        validationRules() {
            return this.builder?.validation_rules;
        },
        nodes() {
            const steps = this.steps;
            if (!steps) return [];
            return Object.entries(steps).map(([id, step]) => ({
                id,
                type: 'special',
                position: { x: step.position?.[0] ?? 0, y: step.position?.[1] ?? 0 },
            }));
        },
        edges() {
            const steps = this.steps; // or this.builder.steps
            const features = this.features; // or this.builder.features
            if (!steps) return [];
            const out = [];

            const uuidPattern = /[a-f\d]{8}-[a-f\d]{4}-[a-f\d]{4}-[a-f\d]{4}-[a-f\d]{12}/gi;

            for (const [stepId, step] of Object.entries(steps)) {

                // --- 1. HANDLE DECISION POINTS ---
                if (step.type === 'decision_point') {
                    // A. Rules Edges
                    if (Array.isArray(step.rules)) {
                        step.rules.forEach((rule, idx) => {
                            if (rule.destination) {
                                out.push({
                                    id: `edge-decision-rule-${stepId}-${idx}`,
                                    type: 'special', // Assuming you use a custom edge type
                                    source: stepId,
                                    target: rule.destination,
                                    sourceHandle: `decision-rule#${stepId}#${idx}`,
                                    animated: true,
                                    label: rule.label || `Rule ${idx + 1}`,
                                    style: { stroke: '#F59E0B' }, // Amber color for logic
                                    data: { step_id: stepId, rule_index: idx, type: 'decision_rule' },
                                });
                            }
                        });
                    }

                    // B. Default/Fallback Edge
                    if (step.default_destination) {
                        out.push({
                            id: `edge-decision-default-${stepId}`,
                            type: 'special',
                            source: stepId,
                            target: step.default_destination,
                            sourceHandle: `decision-default#${stepId}`,
                            animated: true,
                            label: 'Else',
                            style: { stroke: '#94A3B8', strokeDasharray: '5,5' }, // Grey dashed for fallback
                            data: { step_id: stepId, type: 'decision_default' },
                        });
                    }
                    // Decision points usually don't have features, so we can continue
                    continue;
                }

                // --- 2. HANDLE FEATURES (Input/Select) ---
                const fids = step.feature_ids ?? [];
                for (const fid of fids) {
                    const feat = features[fid];
                    if (!feat) continue;

                    if (feat.type === 'input' && feat.next_step_id) {
                        out.push({
                            id: `input-${fid}`,
                            type: 'special',
                            source: stepId,
                            target: feat.next_step_id,
                            sourceHandle: `input#${fid}`,
                            animated: true,
                            data: { feature_id: fid },
                        });
                    }

                    if (feat.type === 'select') {
                        if (feat.source === 'list' && Array.isArray(feat.options)) {
                            feat.options.forEach((opt, idx) => {
                                if (!opt.is_terminal && opt.next_step_id) {
                                    out.push({
                                        id: `static-select-${fid}-${idx}`,
                                        type: 'special',
                                        source: stepId,
                                        target: opt.next_step_id,
                                        sourceHandle: `option-select#${fid}#${idx}`,
                                        animated: true,
                                        data: { feature_id: fid, option_index: idx },
                                    });
                                }
                            });
                        }
                        else if (feat.source === 'code' && feat.content) {
                            // Dynamic code linking logic...
                            const foundTargets = new Set();
                            const matches = feat.content.match(uuidPattern) || [];

                            matches.forEach((targetId) => {
                                if (steps[targetId] && !foundTargets.has(targetId)) {
                                    out.push({
                                        id: `code-select-${fid}-${targetId}`,
                                        type: 'special',
                                        source: stepId,
                                        target: targetId,
                                        sourceHandle: `code-select#${fid}`,
                                        animated: true,
                                        data: { feature_id: fid, is_dynamic: true },
                                    });
                                    foundTargets.add(targetId);
                                }
                            });
                        }
                    }
                }
            }
            return out;
        },
        currentStep() {
            if (!this.currentStepId) return null;
            return this.steps[this.currentStepId] ?? null;
        },
        currentFeature() {
            if (!this.currentFeatureId) return null;
            return this.features[this.currentFeatureId] ?? null;
        },
        currentStepHasContentFeature() {
            if (!this.currentStep) return false;
            const fids = this.currentStep.feature_ids ?? [];
            const features = this.features;
            return fids.some(fid => {
                const f = features[fid];
                return f?.type === 'basic content' || f?.type === 'code content';
            });
        },
        eventTypes: () => EVENT_TYPES,
        canvasActions: () => CANVAS_ACTIONS,
        initialFeatures: () => INITIAL_FEATURES,
        inputValidationRuleOptions: () => INPUT_VALIDATION_RULE_OPTIONS,
    },
    actions: {
        reset() {
            this.version = null;
            this.versionForm = null;
            this.isLoadingVersion = false;
            this.isCreatingVersion = false;
            this.isUpdatingVersion = false;
            this.isDeletingVersion = false;

            this.currentStepId = null;
            this.vueFlowInstance = null;
            this.currentFeatureId = null;
            this.initialStepHistory = [];

            this.simulatorPathNodes = [];
            this.simulatorPathEdges = [];
            this.simulatorPathLogic = {};
            this.simulatorStepResults = {};
            this.simulatorTerminalStepId = null;

            this.featuresModal = null;
            this.stepEditModal = null;
            this.soapApiEventModal = null;
            this.restApiEventModal = null;
            this.inputFeatureModal = null;
            this.canvasActionsModal = null;
            this.decisionPointModal = null;
            this.deleteVersionModal = null;
            this.versionDetailsModal = null;
            this.codeContentEditorModal = null;
            this.basicContentEditorModal = null;

            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.versionForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.versionForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.versionForm);
        },
        setVersion(version) {
            this.version = version;
        },
        setVersionForm(version, saveState = true) {

            this.versionForm = {
                number: version.number,
                builder: version.builder,
                description: version.description
            };

            const collections = ['steps', 'features', 'validation_rules'];

            collections.forEach(key => {
                const value = this.versionForm.builder[key];

                if (value.length === 0) {
                    this.versionForm.builder[key] = {};
                }
            });

            if (saveState) this.saveOriginalState('Original version');
        },

        /**
         * Core method to add any step to the Vue Flow canvas.
         * Supports positioning overrides and relative placement.
         */
        addStepToCanvas(config = {}, options = {}, zoom = true) {
            const newId = uuidv4();
            const { nextToCurrentStep, position } = options;
            const steps = Object.values(this.builder.steps || {});

            let x, y;

            // 1. Priority: Use explicit position if provided
            if (position && position.x !== undefined && position.y !== undefined) {
                x = position.x;
                y = position.y;
            }
            // 2. Secondary: Place relative to the currently active/selected step
            else if (nextToCurrentStep && this.currentStep) {
                const currentPos = this.currentStep.position || [0, 0];
                x = currentPos[0] + 350;
                y = currentPos[1];
            }
            // 3. Default: Find the right-most edge of the existing flow
            else {
                if (steps.length > 0) {
                    const maxX = Math.max(...steps.map(s => s.position[0]));
                    const rightMostStep = steps.find(s => s.position[0] === maxX);

                    x = maxX + 350;
                    y = rightMostStep ? rightMostStep.position[1] : 50;
                } else {
                    x = 50;
                    y = 50;
                }
            }

            // Create the new step object
            this.versionForm.builder.steps[newId] = {
                name: config.name || 'New Step',
                type: config.type,
                position: [x, y],
                ...config.data // Inject type-specific properties (feature_ids, rules, etc.)
            };

            // Auto-assign as initial step if canvas was empty
            if (this.builder.initial_step_id == null) {
                this.builder.initial_step_id = newId;
            }

            // Vue Flow Viewport Focus: Smoothly zoom into the new node
            if (zoom && this.vueFlowInstance) {
                const padding = Object.keys(this.builder.steps).length === 1 ? 2 : 4;
                const hook = this.vueFlowInstance.onNodesInitialized(() => {
                    this.vueFlowInstance.fitView({
                        nodes: [newId],
                        duration: 800,
                        padding: padding
                    });
                    hook.off();
                });
            }

            return newId;
        },
        /**
         * AUTO-LAYOUT: SMART STACKING & GAP MANAGEMENT
         * Fixed: Fully traverses features AND decision logic to strictly enforce logical sequence.
         */
        autoLayoutNodes({ zoom = true, autoLayout = true, focusNodeId = null } = {}) {
            const builder = this.builder;
            const steps = builder.steps;
            const features = builder.features;
            const initialId = builder.initial_step_id;

            if (!initialId || !steps[initialId]) return;

            if (autoLayout) {
                const horizontalSpacing = 450;
                const verticalSpacing = 40;
                const columns = [];
                const visited = new Set();

                // 1. Height Calculator
                const getNodeHeight = (id) => {
                    const el = document.querySelector(`[data-id="${id}"]`);
                    if (el) return el.offsetHeight;
                    let est = 120;
                    const step = steps[id];
                    if (step?.event_ids) est += (step.event_ids.length * 62);
                    if (step?.feature_ids) {
                        step.feature_ids.forEach(fid => {
                            const f = features[fid];
                            if (f?.type === 'select') est += (f.options?.length || 0) * 50 + 80;
                            else if (f?.type === 'input') est += 110;
                            else est += 100;
                        });
                    }
                    return est;
                };

                // 2. LOGIC EXTENSION: Traverse both Features and Decision Rules
                const getOrderedChildren = (stepId) => {
                    const step = steps[stepId];
                    if (!step) return [];

                    const orderedIds = [];

                    // A. If it's a Screen with Features
                    if (step.feature_ids && step.feature_ids.length > 0) {
                        step.feature_ids.forEach(fid => {
                            const feat = features[fid];
                            if (!feat) return;

                            // Select Options (Strict Order)
                            if (feat.type === 'select' && Array.isArray(feat.options)) {
                                feat.options.forEach(opt => {
                                    if (opt.next_step_id) orderedIds.push(opt.next_step_id);
                                });
                            }
                            // Input / Direct Links
                            if (feat.next_step_id) {
                                orderedIds.push(feat.next_step_id);
                            }
                        });
                    }

                    // B. If it's a Decision Point (Logic Gate)
                    if (step.type === 'decision_point') {
                        // 1. Check Rules
                        if (Array.isArray(step.rules)) {
                            step.rules.forEach(rule => {
                                if (rule.destination) orderedIds.push(rule.destination);
                            });
                        }
                        // 2. Check Default Destination
                        if (step.default_destination) {
                            orderedIds.push(step.default_destination);
                        }
                    }

                    return orderedIds;
                };

                // 3. BFS Runner
                const runBFS = (startNodes) => {
                    const queue = [...startNodes];

                    while (queue.length > 0) {
                        const { id, level } = queue.shift();

                        if (visited.has(id)) continue;
                        visited.add(id);

                        if (!columns[level]) columns[level] = [];
                        columns[level].push(id);

                        // Because we find children via LOGIC (not position),
                        // dragging nodes around manually will not affect this order.
                        getOrderedChildren(id).forEach(childId => {
                            if (!visited.has(childId)) {
                                queue.push({ id: childId, level: level + 1 });
                            }
                        });
                    }
                };

                // 4. Execution
                // A. Main Tree (The Anchor)
                runBFS([{ id: initialId, level: 0 }]);

                // B. Orphans (True disconnected islands)
                const orphanIds = Object.keys(steps)
                    .filter(id => !visited.has(id))
                    .sort((a, b) => (steps[a].position[0] - steps[b].position[0]));

                orphanIds.forEach(orphanId => {
                    if (visited.has(orphanId)) return;
                    const startColumn = columns.length; // Start in a clean new column
                    runBFS([{ id: orphanId, level: startColumn }]);
                });

                // 5. Apply Coordinates
                columns.forEach((columnSteps, colIdx) => {
                    if (!columnSteps) return;

                    const totalNodesHeight = columnSteps.reduce((sum, id) => sum + getNodeHeight(id), 0);
                    const totalGapsHeight = (columnSteps.length - 1) * verticalSpacing;
                    const totalColumnHeight = totalNodesHeight + totalGapsHeight;

                    let currentY = 300 - (totalColumnHeight / 2);

                    columnSteps.forEach((stepId) => {
                        steps[stepId].position = [
                            colIdx * horizontalSpacing + 50,
                            currentY
                        ];
                        currentY += getNodeHeight(stepId) + verticalSpacing;
                    });
                });

                this.saveState('Auto-arranged tree layout');
            }

            // Camera Logic
            if (this.vueFlowInstance && (zoom || focusNodeId)) {
                setTimeout(() => {
                    const targetNodes = focusNodeId ? [focusNodeId] : Object.keys(this.builder.steps);
                    const padding = targetNodes.length === 1 ? 2.5 : 0.2;
                    this.vueFlowInstance.fitView({ nodes: targetNodes, duration: 800, padding: padding });
                }, 200);
            }
        },
        /**
         * Wrapper for adding Interactive Screens (USSD Menus/Forms)
         */
        addInteractiveScreenStep(name = null, options = {}, zoom = true) {
            const id = this.addStepToCanvas({
                name: name || 'New Screen',
                type: 'interactive_screen',
                data: {
                    is_terminal: false,
                    feature_ids: []
                }
            }, options, zoom);

            this.saveState('Added Interactive Screen');
            return id;
        },

        /**
         * Wrapper for adding Logic Gates
         */
        addDecisionPointStep(name = null, options = {}, zoom = true) {
            const id = this.addStepToCanvas({
                name: name || 'Decision Point',
                type: 'decision_point',
                data: {
                    source: 'manual',
                    rules: [
                        {
                            label: 'Default Rule',
                            checks: [{ variable: 'status', operator: '==', value: 'ACTIVE' }],
                            destination: null
                        }
                    ],
                    default_destination: null
                }
            }, options, zoom);

            this.saveState('Added Decision Point');
            return id;
        },
        removeStep(stepId) {
            const builder = this.builder;
            if (!builder.steps[stepId]) return;

            // Handle start-step logic before deletion
            if (builder.initial_step_id === stepId) {
                this.reassignInitialStep(stepId);
            }

            // Cleanup features if it was an interactive screen
            if (builder.steps[stepId].type === 'interactive_screen') {
                const fids = builder.steps[stepId].feature_ids || [];
                fids.forEach(fid => delete builder.features[fid]);
            }

            delete builder.steps[stepId];
            this.saveState('Removed Step');
        },
        toggleStepTermination(stepId) {
            const step = this.builder.steps[stepId];
            if (!step) return;

            step.is_terminal = !step.is_terminal;

            if (step.is_terminal) {
                const typesToRemove = ['input', 'select'];
                this.clearFeaturesByType(step, typesToRemove);
            }

            const status = step.is_terminal ? 'end' : 'continue';
            return status;
        },
        setCurrentStepId(stepId) {
            this.currentStepId = stepId;
        },
        setCurrentFeatureId(featureId) {
            this.currentFeatureId = featureId;
        },
        setInitialStepId(stepId) {
            this.builder.initial_step_id = stepId;
        },
        addToInitialStepHistory(newId) {
            if (!newId) return;
            this.initialStepHistory = this.initialStepHistory.filter(id => id !== newId);
            this.initialStepHistory.push(newId);
            if (this.initialStepHistory.length > 5) this.initialStepHistory.shift();
        },
        reassignInitialStep(currentId) {
            const builder = this.versionForm.builder;
            const allStepIds = Object.keys(builder.steps || {});

            // 1. Filter history: reverse it, remove current, ensure they still exist
            const validHistory = [...this.initialStepHistory]
                .reverse()
                .filter(id => id !== currentId && allStepIds.includes(id));

            if (validHistory.length > 0) {
                builder.initial_step_id = validHistory[0];
                return { success: true, type: 'history', name: builder.steps[validHistory[0]].name };
            }

            // 2. Fallback to first available in object
            const absoluteFirst = allStepIds.find(id => id !== currentId);
            if (absoluteFirst) {
                builder.initial_step_id = absoluteFirst;
                return { success: true, type: 'fallback', name: builder.steps[absoluteFirst].name };
            }

            // 3. Last man standing
            builder.initial_step_id = currentId;
            return { success: false, type: 'last_man' };
        },
        removeStep(stepId) {
            const builder = this.builder;
            const step = builder.steps[stepId];
            if (!step) return;

            // 1. Cleanup features OWNED by this step
            (step.feature_ids ?? []).forEach(fid => {
                const feature = builder.features[fid];
                // If it's an input, clean up its validation rules first
                if (feature?.type === 'input' && feature.validation_rule_ids) {
                    feature.validation_rule_ids.forEach(vid => delete builder.validation_rules[vid]);
                }
                delete builder.features[fid];
            });

            // 2. DISCONNECT any other steps pointing to THIS step
            // We scan all features in the builder to unset next_step_id
            Object.values(builder.features).forEach(feat => {
                // Handle Input features
                if (feat.type === 'input' && feat.next_step_id === stepId) {
                    feat.next_step_id = null;
                }

                // Handle Select features (list source)
                if (feat.type === 'select' && Array.isArray(feat.options)) {
                    feat.options.forEach(opt => {
                        if (opt.next_step_id === stepId) {
                            opt.next_step_id = null;
                        }
                    });
                }
            });

            // 3. Handle Initial Step logic
            const isStart = builder.initial_step_id === stepId;
            if (isStart) {
                // Use your existing logic to find a new starting point
                this.reassignInitialStep(stepId);
            }

            // 4. Finally, delete the step itself
            delete builder.steps[stepId];
        },

        addFeature(type, overrides = {}) {

            const fid = uuidv4();
            const step = this.currentStep;

            // Merge default template with any specific overrides
            const newFeature = {
                ...this.getFeatureTemplate(type),
                ...overrides
            };

            // Commit to state
            this.builder.features[fid] = newFeature;
            step.feature_ids = step.feature_ids ?? [];
            step.feature_ids.push(fid);

            this.currentFeatureId = fid;
            return { fid, feature: newFeature };
        },
        addBasicContentFeature(overrides) {
            this.clearFeaturesByType(this.currentStep, ['basic content', 'code content']);
            this.addFeature('basic content', overrides);
        },
        addCodeContentFeature(overrides) {
            this.clearFeaturesByType(this.currentStep, ['basic content', 'code content']);
            this.addFeature('code content', overrides);
        },
        addInputFeature(overrides) {
            this.clearFeaturesByType(this.currentStep, ['input', 'select']);
            if (!this.currentStepHasContentFeature) {
                this.currentFeatureId = null;
                this.featuresModal?.hideModal();
                // Prompt the user to create content AND the input feature together
                this.inputFeatureModal?.showModal();
                return;
            }

            this.addFeature('input', overrides);
        },
        addSelectFeature(overrides) {
            // 1. Clear existing interactive features (Input or Select)
            this.clearFeaturesByType(this.currentStep, ['input', 'select']);

            // 2. Check if the step has a message/instruction yet
            if (!this.currentStepHasContentFeature) {
                this.currentFeatureId = null;
                this.featuresModal?.hideModal();
                // Prompt the user to create content AND the select feature together
                this.selectFeatureModal?.showModal();
                return;
            }

            // 3. Otherwise, just add it directly
            this.addFeature('select', overrides);
        },
        removeFeature(stepId, featureId) {
            const step = this.builder.steps[stepId];
            if (!step?.feature_ids) return;

            const feature = this.builder.features[featureId];
            if (!feature) return;

            // Cleanup: If it's an input, delete all associated validation rules first
            if (feature.type === 'input' && feature.validation_rule_ids) {
                feature.validation_rule_ids.forEach(vid => {
                    delete this.builder.validation_rules[vid];
                });
            }

            // 1. Remove the reference from the step
            step.feature_ids = step.feature_ids.filter(id => id !== featureId);

            // 2. Delete the actual feature data
            delete this.builder.features[featureId];
        },
        switchFeatureContentType(featureId, toType) {
            const f = this.builder.features[featureId];

            if (toType === 'code content') {
                f.type = 'code content';
                f.language = f.language || 'php';
                // Only set default if content is truly empty/null
                if (!f.content || f.content.trim() === '') {
                    f.content = '';
                }
            } else {
                f.type = 'basic content';
                // Strip out PHP tags if moving from code to basic?
                // Usually, it's safer just to keep what they wrote.
                if (!f.content || f.content.trim() === '') {
                    f.content = '';
                }
            }
        },
        getFeatureTemplate(type) {
            const defaults = {
                'basic content': {
                    type,
                    content: ''
                },
                'code content': {
                    type,
                    language: 'php',
                    content: ""
                },
                'input': {
                    type: 'input',
                    variable: 'value',
                    next_step_id: null,
                    validation_rule_ids: []
                },
                'select': {
                    type: 'select',
                    variable: 'choice',
                    source: 'list', // 'list' or 'code'
                    options: [],    // Used for source: 'list'
                    language: 'php', // Used for source: 'code'
                    content: "",
                },
            };
            return defaults[type] || { type };
        },
        clearFeaturesByType(step, typesToRemove = []) {
            if (!step.feature_ids || !typesToRemove.length) return;

            step.feature_ids = step.feature_ids.filter(fid => {
                const feature = this.builder.features[fid];

                // Check if this feature's type is in our "hit list"
                const shouldRemove = typesToRemove.includes(feature?.type);

                if (shouldRemove) {
                    // 1. Clean up the actual data record
                    delete this.builder.features[fid];
                    // 2. Return false to remove it from step.feature_ids
                    return false;
                }

                return true;
            });
        },
        getValidationRuleLabel(type) {
            return INPUT_VALIDATION_RULE_OPTIONS.find(o => o.value === type)?.label || 'Validation rule';
        },
        getValidationRuleDefaultMessage(rule) {
            if (!rule) return '';
            const t = rule.type;
            const v = rule.value;
            switch (t) {
                case 'alpha': return 'Please enter letters only (spaces allowed)';
                case 'numeric': return 'Please enter numbers only (spaces allowed)';
                case 'alphanumeric': return 'Please enter letters and numbers only (spaces allowed)';
                case 'min_length': return `Please enter ${v || 'the minimum number of'} or more characters`;
                case 'max_length': return `Please enter no more than ${v || 'the maximum number of'} characters`;
                case 'equal_length': return `Please enter exactly ${v || 'the required number of'} characters`;
                case 'eq': return `Please enter the value ${v || ''}`.trim();
                case 'neq': return `Please enter any value except ${v || ''}`.trim();
                case 'lt': return `Please enter numbers less than ${v || ''}`.trim();
                case 'lte': return `Please enter numbers less than or equal to ${v || ''}`.trim();
                case 'gt': return `Please enter numbers greater than ${v || ''}`.trim();
                case 'gte': return `Please enter numbers greater than or equal to ${v || ''}`.trim();
                case 'between_inclusive': return `Please enter numbers between ${rule.min ?? 'min'} and ${rule.max ?? 'max'}`;
                case 'between_exclusive': return `Please enter numbers between ${rule.min ?? 'min'} and ${rule.max ?? 'max'}`;
                case 'email': return 'Please provide a valid email address e.g example@gmail.com';
                case 'money': return 'Please provide a valid money format e.g "35", "35.5" or "35.50"';
                case 'date_ddmmyyyy': return 'Please enter a valid date (DDMMYYYY) e.g 02082020';
                case 'date_dd_mm_yyyy': return 'Please enter a valid date (DD/MM/YYYY) e.g 02/08/2020';
                case 'date_dd_mm_yyyy_dash': return 'Please enter a valid date (DD-MM-YYYY) e.g 02-08-2020';
                case 'no_spaces': return 'Do not use spaces';
                case 'regex': return 'Custom regex validation error';
                default: return 'Please enter a valid value';
            }
        },
        getValidationRuleSummary(rule) {
            if (!rule) return '';
            const t = rule.type;
            if (['min_length', 'max_length', 'equal_length'].includes(t)) return rule.value ? `Length: ${rule.value}` : 'Set length';
            if (['eq', 'neq', 'lt', 'lte', 'gt', 'gte'].includes(t)) return rule.value ? `Value: ${rule.value}` : 'Set value';
            if (['between_inclusive', 'between_exclusive'].includes(t)) return (rule.min != null || rule.max != null) ? `Range: ${rule.min ?? '…'} to ${rule.max ?? '…'}` : 'Set range';
            if (t === 'regex') return rule.pattern ? `Pattern: ${rule.pattern}` : 'Set pattern';
            return rule.message || 'Rule';
        },
        addValidationRule(featureId) {
            const feat = this.builder.features[featureId];
            if (!feat || feat.type !== 'input') return null;

            const vid = uuidv4();

            // Ensure the array exists and remains reactive
            if (!feat.validation_rule_ids) {
                feat.validation_rule_ids = [];
            }

            // Initialize with safe defaults matching your UI expectations
            const rule = {
                type: 'alpha',
                value: '1',
                min: '1',
                max: '10',
                pattern: '^[A-Za-z]+$',
                message: ''
            };

            // Set the default message based on the initial type
            rule.message = this.getValidationRuleDefaultMessage(rule);

            // 1. Register the rule in the global dictionary
            this.builder.validation_rules[vid] = rule;

            // 2. Link the rule to the feature
            feat.validation_rule_ids.push(vid);

            return vid;
        },
        removeValidationRule(featureId, ruleId) {
            const feat = this.builder.features[featureId];
            if (feat?.validation_rule_ids) feat.validation_rule_ids = feat.validation_rule_ids.filter(id => id !== ruleId);
            delete this.builder.validation_rules[ruleId];
        },
        addEvent(stepId, timing, type) {
            const eid = uuidv4();
            const step = this.builder.steps[stepId];

            if (!step) {
                console.error(`Step ${stepId} not found.`);
                return null;
            }

            // Ensure the global events object exists
            if (!this.builder.events) {
                this.builder.events = {};
            }

            const isSoap = type === 'soap api';

            const baseEvent = {
                id: eid,
                type,
                timing,
                source: 'manual',
                url: '',
                // Set contextual defaults for headers
                headers: [
                    {
                        key: 'Content-Type',
                        value: isSoap ? 'text/xml; charset=utf-8' : 'application/json'
                    }
                ],
                // Set sensible variable defaults based on type
                response_variable: isSoap ? 'soapResponse' : 'apiResponse',
                status_variable: isSoap ? 'soapStatus' : 'apiStatus',
                // Always initialize code_content to prevent null errors in Code Mode
                code_content: isSoap
                    ? "<?php\n\nreturn [\n    'url' => 'https://api.example.com/services',\n    'envelope' => '<soap:Envelope>...</soap:Envelope>',\n    'action' => 'http://tempuri.org/Action'\n];"
                    : "<?php\n\nreturn [\n    'method' => 'GET',\n    'url' => 'https://api.example.com/resource'\n];"
            };

            const specificData = isSoap ? {
                soap_action: '',
                soap_version: '1.1',
                envelope: '<?xml version="1.0" encoding="utf-8"?>\n<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">\n  <soap:Body>\n    \n  </soap:Body>\n</soap:Envelope>',
            } : {
                method: 'GET',
                body_type: 'form',
                body_form: [{ key: '', value: '' }],
                body: '',
            };

            // 1. Create the full event object
            const newEvent = { ...baseEvent, ...specificData };

            // 2. Commit to the global events dictionary first (Crucial for reactivity)
            this.builder.events[eid] = newEvent;

            // 3. Link the event to the step
            if (!step.event_ids) {
                step.event_ids = [];
            }
            step.event_ids.push(eid);

            // 4. Set as current event so modals know what to edit
            this.currentEventId = eid;

            // 5. Save state for undo/redo history
            this.saveState(`Added ${type} (${timing})`);

            return eid;
        },
        removeEvent(stepId, eventId) {
            const step = this.builder.steps[stepId];
            if (step) step.event_ids = step.event_ids.filter(id => id !== eventId);
            delete this.builder.events[eventId];
        },


        onConnect(params) {
            const { target, sourceHandle } = params;
            if (!sourceHandle) return;

            // 1. Handle Decision Point Rules (Format: "decision-rule#stepId#ruleIndex")
            if (sourceHandle.startsWith('decision-rule#')) {
                const parts = sourceHandle.split('#');
                const stepId = parts[1];
                const ruleIdx = parseInt(parts[2], 10);

                const step = this.builder.steps[stepId];
                if (step && step.rules && step.rules[ruleIdx]) {
                    step.rules[ruleIdx].destination = target;
                }
                return;
            }

            // 2. Handle Decision Point Default/Fallback (Format: "decision-default#stepId")
            if (sourceHandle.startsWith('decision-default#')) {
                const parts = sourceHandle.split('#');
                const stepId = parts[1];

                const step = this.builder.steps[stepId];
                if (step) {
                    step.default_destination = target;
                }
                return;
            }

            // 3. Handle Inputs (Format: "input#fid")
            if (sourceHandle.startsWith('input#')) {
                const fid = sourceHandle.split('#')[1];
                const feat = this.builder.features[fid];
                if (feat) {
                    feat.next_step_id = target;
                }
                return;
            }

            // 4. Handle Code-Driven Menus (Format: "code-select#fid")
            // Logic: Helper to copy target ID to clipboard and open editor
            if (sourceHandle.startsWith('code-select#')) {
                const fid = sourceHandle.split('#')[1];
                const feat = this.builder.features[fid];

                if (feat) {
                    // A. Copy UUID to clipboard
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(target);
                        // Ensure you have access to notificationState or use console
                        if (typeof notificationState === 'function') {
                            notificationState().showSuccessNotification(
                                `Step ID copied! Paste it into your ${feat.language} script.`
                            );
                        }
                    }

                    // B. Automatically open the modal for this feature
                    // Find which step this feature belongs to
                    const stepId = Object.keys(this.builder.steps).find(sid =>
                        this.builder.steps[sid].feature_ids?.includes(fid)
                    );

                    if (stepId) {
                        this.setCurrentStepId(stepId);
                        this.setCurrentFeatureId(fid);
                        if (this.selectFeatureModal) {
                            this.selectFeatureModal.showModal();
                        }
                    }
                }
                return;
            }

            // 5. Handle Static List Options (Format: "option-select#fid#idx")
            if (sourceHandle.startsWith('option-select#')) {
                const parts = sourceHandle.split('#');
                const fid = parts[1];
                const optIdx = parseInt(parts[2], 10);

                const feat = this.builder.features[fid];
                if (feat?.options?.[optIdx]) {
                    feat.options[optIdx].next_step_id = target;
                }
            }
        },
        onNodesChange(changes) {
            for (const c of changes || []) {
                if (c.type === 'position' && c.id != null && c.position != null) {
                    const step = this.builder.steps[c.id];
                    if (step) step.position = [c.position.x, c.position.y];
                }
            }
        },
        onEdgesChange(changes) {
            (changes || []).filter(c => c.type === 'remove' && c.id).forEach(c => this.removeInputEdge(c.id));
        },
        removeInputEdge(edgeId) {
            const edges = this.edges; // Use the computed property
            const edge = edges.find(e => e.id === edgeId);
            if (!edge || !edge.data) return;

            // Handle Decision Rules
            if (edge.data.type === 'decision_rule') {
                const step = this.builder.steps[edge.data.step_id];
                if (step && step.rules && step.rules[edge.data.rule_index]) {
                    step.rules[edge.data.rule_index].destination = null;
                }
                return;
            }

            // Handle Decision Default
            if (edge.data.type === 'decision_default') {
                const step = this.builder.steps[edge.data.step_id];
                if (step) {
                    step.default_destination = null;
                }
                return;
            }

            // Handle Features (Existing logic)
            if (edge.data.feature_id) {
                const f = this.builder.features[edge.data.feature_id];
                if (f?.type === 'input') f.next_step_id = null;
                if (f?.type === 'select' && edge.data.option_index != null && f.options?.[edge.data.option_index]) {
                    f.options[edge.data.option_index].next_step_id = null;
                }
            }
        },

        /**
         * Recursively finds the logical path between two nodes, including
         * intermediate decision points and edges.
         */
        findLogicalPath(currentId, targetId, visitedNodes = [], visitedEdges = [], visitedLogic = {}) {
            if (currentId === targetId) return { nodes: visitedNodes, edges: visitedEdges, logic: visitedLogic };

            if (visitedNodes.length > 20) return null;

            const outgoingEdges = this.edges.filter(e => e.source === currentId);

            for (const edge of outgoingEdges) {
                if (visitedEdges.includes(edge.id)) continue;

                // If the edge has a label (like "RULE #1" or "Else"), track it
                const newLogic = { ...visitedLogic };
                if (edge.label) {
                    newLogic[currentId] = edge.label;
                }

                const result = this.findLogicalPath(
                    edge.target,
                    targetId,
                    [...visitedNodes, edge.target],
                    [...visitedEdges, edge.id],
                    newLogic
                );

                if (result) return result;
            }
            return null;
        },
        addToSimulatorPath(stepId, isStop, message = null, logicLabel = null, sourceStepId = null) {
            // 1. If this is the start of a session (no nodes in path yet)
            if (!this.simulatorPathNodes.length) {
                this.simulatorPathNodes.push(stepId);
                if (message) this.simulatorStepResults[stepId] = message;
                if (isStop) this.simulatorTerminalStepId = stepId;
                return;
            }

            // 2. Store the user's reply/logic on the SOURCE step (where they were)
            if (sourceStepId && logicLabel) {
                this.simulatorPathLogic[sourceStepId] = logicLabel;
            }

            const lastStepId = this.simulatorPathNodes[this.simulatorPathNodes.length - 1];

            // Ignore if the step hasn't changed
            if (lastStepId === stepId) return;

            // 3. Trace the path to bridge gaps (handles intermediate Decision Points)
            const trace = this.findLogicalPath(lastStepId, stepId);

            if (trace) {
                // A. Push Nodes
                trace.nodes.forEach(id => {
                    if (!this.simulatorPathNodes.includes(id)) this.simulatorPathNodes.push(id);
                });

                // B. Push Edges
                trace.edges.forEach(id => {
                    if (!this.simulatorPathEdges.includes(id)) this.simulatorPathEdges.push(id);
                });

                // C. APPLY MATCHED RULES (This was missing)
                if (trace.logic) {
                    Object.entries(trace.logic).forEach(([nodeId, label]) => {
                        this.simulatorPathLogic[nodeId] = label;
                    });
                }

            } else {
                // Fallback
                if (!this.simulatorPathNodes.includes(stepId)) this.simulatorPathNodes.push(stepId);
            }

            // 4. Store the USSD message for the NEW current step
            if (message) this.simulatorStepResults[stepId] = message;

            // 5. Fallback for immediate logic assignment
            if (logicLabel && !sourceStepId) {
                this.simulatorPathLogic[stepId] = logicLabel;
            }

            if (isStop) {
                this.simulatorTerminalStepId = stepId;
            }
        },
        clearSimulatorPath() {
            this.simulatorPathNodes = [];
            this.simulatorPathEdges = [];
            this.simulatorPathLogic = {};
            this.simulatorStepResults = {};
            this.simulatorTerminalStepId = null;
        },
    },
});
