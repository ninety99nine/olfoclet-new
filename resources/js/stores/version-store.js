import axios from 'axios';
import { v4 as uuidv4 } from 'uuid';
import { defineStore } from 'pinia';
import { useRouter } from 'vue-router';
import { useFormStore } from '@Stores/form-store.js';
import { useNotificationStore } from '@Stores/notification-store.js';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

const CANVAS_ACTIONS = [
    { value: 'new_step', label: 'Add new step', description: 'Add a new step to your flow.', icon: 'LayoutGrid' },
    { value: 'condition', label: 'Add condition', description: 'Create a condition to decide which step to show.', icon: 'GitBranch' },
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

        featuresModal: null,
        stepEditModal: null,
        inputFeatureModal: null,
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
            return Object.entries(steps).map(([id, step]) => ({
                id,
                type: 'special',
                position: { x: step.position?.[0] ?? 0, y: step.position?.[1] ?? 0 },
            }));
        },
        edges() {
            const steps = this.steps;
            const features = this.features;
            const out = [];
            for (const [stepId, step] of Object.entries(steps)) {
                const fids = step.feature_ids ?? [];
                for (const fid of fids) {
                    const feat = features[fid];
                    if (!feat) continue;
                    if (feat.type === 'input' && feat.next_step_id) {
                        out.push({
                            id: `e-${fid}-${feat.next_step_id}`,
                            type: 'special',
                            source: stepId,
                            target: feat.next_step_id,
                            sourceHandle: fid,
                            animated: true,
                            data: { feature_id: fid },
                        });
                    }
                    if (feat.type === 'select' && Array.isArray(feat.options)) {
                        feat.options.forEach((opt, idx) => {
                            if (opt.next_step_id) {
                                out.push({
                                    id: `e-${fid}-${idx}-${opt.next_step_id}`,
                                    type: 'special',
                                    source: stepId,
                                    target: opt.next_step_id,
                                    sourceHandle: `select-${fid}-${idx}`,
                                    animated: true,
                                    data: { feature_id: fid, optionIndex: idx },
                                });
                            }
                        });
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

            this.featuresModal = null;
            this.stepEditModal = null;
            this.inputFeatureModal = null;
            this.canvasActionsModal = null;
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





        addNewConditionToCanvas() {},
        addNewStepToCanvas(stepName = null, nextToCurrentStep = false) {
            const newId = uuidv4();
            const steps = Object.values(this.builder.steps);

            let x,y;

            if (nextToCurrentStep) {
                const currentStep = this.currentStep;
                x = currentStep.position[0] + 350;
                y = currentStep.position[1];
            }else{

                // Default position if canvas is empty
                x = 50;
                y = 50;

                if (steps.length > 0) {
                    // Find the right-most boundary of all existing steps
                    const maxX = Math.max(...steps.map(s => s.position[0]));

                    // Find the step(s) at that right-most boundary to align vertically
                    const rightMostStep = steps.find(s => s.position[0] === maxX);

                    x = maxX + 350; // Standard horizontal spacing
                    y = rightMostStep.position[1]; // Align with the right-most step
                }

            }

            // Use Vue.set or direct assignment depending on your Vue version
            // but ensure we are modifying the reactive versionForm
            this.versionForm.builder.steps = {
                ...this.versionForm.builder.steps,
                [newId]: {
                    name: stepName || 'New Step',
                    feature_ids: [],
                    position: [x, y]
                }
            };

            if(this.builder.initial_step_id == null) {
                this.builder.initial_step_id = newId;
            }

            if (this.vueFlowInstance) {

                const padding = Object.keys(this.builder.steps).length == 1 ? 2 : 4;

                const hook = this.vueFlowInstance.onNodesInitialized((nodes) => {
                    this.vueFlowInstance.fitView({
                        nodes: [newId],
                        duration: 800,
                        padding: padding
                    });
                    hook.off();
                });

            }

            // Helpful for chaining actions (like opening the edit modal immediately)
            return newId;
        },
        removeStep(stepId) {
            const isStart = this.versionForm.builder.initial_step_id === stepId;
            if (isStart) this.reassignInitialStep(stepId);
            delete this.versionForm.builder.steps[stepId];
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
            const step = this.builder.steps[stepId];
            if (!step) return;
            (step.feature_ids ?? []).forEach(fid => delete this.builder.features[fid]);
            delete this.builder.steps[stepId];
            if (this.builder.initial_step_id === stepId) {
                this.setInitialStepId(Object.keys(this.builder.steps)[0] ?? null);
            }
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
                    f.content = '<?php\n\n\treturn \'Welcome message or instructions\';\n\n?>';
                }
            } else {
                f.type = 'basic content';
                // Strip out PHP tags if moving from code to basic?
                // Usually, it's safer just to keep what they wrote.
                if (!f.content || f.content.trim() === '') {
                    f.content = 'Welcome message or instructions';
                }
            }
        },
        getFeatureTemplate(type) {
            const defaults = {
                'basic content': {
                    type,
                    content: 'Welcome message or instructions'
                },
                'code content': {
                    type,
                    language: 'php',
                    content: "<?php\n\n\treturn 'Welcome message or instructions';\n\n?>"
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
                    content: "<?php\n\nreturn [\n\t['label' => 'Option 1', 'next_step_id' => null],\n\t['label' => 'Option 2', 'next_step_id' => null],\n];\n\n?>",
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




        onConnect(params) {
            const { target, sourceHandle } = params;
            const handleId = sourceHandle || '';
            let fid = handleId;
            let optIdx = null;
            if (handleId.startsWith('select-')) {
                const parts = handleId.split('-');
                if (parts.length >= 3) {
                    optIdx = parseInt(parts[parts.length - 1], 10);
                    fid = parts.slice(1, -1).join('-');
                }
            }
            const feat = this.builder.features[fid];
            if (!feat) return;
            if (feat.type === 'input') {
                feat.next_step_id = target;
                return;
            }
            if (feat.type === 'select' && optIdx != null) {
                if (!feat.options) feat.options = [];
                if (!feat.options[optIdx]) feat.options[optIdx] = { value: '', label: '', next_step_id: null };
                feat.options[optIdx].next_step_id = target;
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
            const edges = this.edges;
            const edge = edges.find(e => e.id === edgeId);
            if (!edge?.data?.feature_id) return;
            const f = this.builder.features[edge.data.feature_id];
            if (f?.type === 'input') f.next_step_id = null;
            if (f?.type === 'select' && edge.data.optionIndex != null && f.options?.[edge.data.optionIndex]) {
                f.options[edge.data.optionIndex].next_step_id = null;
            }
        },
    },
});
