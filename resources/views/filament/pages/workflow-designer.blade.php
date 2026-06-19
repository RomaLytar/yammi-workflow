<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/drawflow@0.0.59/dist/drawflow.min.css">

    <style>
        .wf-node { padding: 4px; }
        .wf-key { width: 110px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; font-weight: 600; font-size: 13px; }
        .drawflow .drawflow-node { border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
        .dark .drawflow .drawflow-node { background: #1f2937; border-color: #374151; }
    </style>

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <select wire:model="workflowKey" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
            <option value="">Start a new workflow…</option>
            @foreach ($this->workflowOptions() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <x-filament::button size="sm" color="gray" wire:click="load" icon="heroicon-o-arrow-down-tray">
            Load for editing
        </x-filament::button>
    </div>

    <div
        wire:ignore
        x-data="workflowDesigner(@js($graph))"
        x-init="boot()"
        class="space-y-3"
    >
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::button color="gray" type="button" x-on:click="addState()" icon="heroicon-o-plus">
                Add state
            </x-filament::button>

            <label class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-300">
                Initial:
                <input x-model="initialKey" class="rounded-md border-gray-300 text-sm dark:bg-gray-800" style="width: 110px" placeholder="draft">
            </label>

            <x-filament::button type="button" x-on:click="sync().then(() => $wire.save())" icon="heroicon-o-check">
                Save as new version
            </x-filament::button>

            <span class="text-xs text-gray-400" x-text="hint"></span>
        </div>

        <p class="text-xs text-gray-500">
            Drag a node to move it. Drag from a node's right dot to another node's left dot to add a transition.
            Right-click a node or a connection to delete it. Edit a state name in its box.
        </p>

        <div
            x-ref="canvas"
            class="fi-wo-canvas"
            style="height: 52vh; border: 1px solid rgb(229 231 235); border-radius: 0.75rem; background:
                radial-gradient(rgb(229 231 235) 1px, transparent 1px); background-size: 18px 18px;"
        ></div>
    </div>

    <div class="mt-6 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Conditions (no-code rules)</h3>
                <p class="text-sm text-gray-500">A transition is allowed only when its rules pass — set here, no code.</p>
            </div>
            <x-filament::button size="sm" color="gray" wire:click="addRule" icon="heroicon-o-plus">
                Add rule
            </x-filament::button>
        </div>

        @forelse ($conditions as $i => $rule)
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                <select wire:model="conditions.{{ $i }}.from" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">from…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-gray-400">&rarr;</span>
                <select wire:model="conditions.{{ $i }}.to" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">to…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">allowed if</span>
                <input wire:model="conditions.{{ $i }}.field" placeholder="field (e.g. amount)" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                <select wire:model="conditions.{{ $i }}.operator" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    @foreach ($this->operatorOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input wire:model="conditions.{{ $i }}.value" placeholder="value" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                <button type="button" wire:click="removeRule({{ $i }})" class="text-danger-600 hover:text-danger-500">&times;</button>
            </div>
        @empty
            <p class="text-sm text-gray-400">No rules yet — every declared transition is allowed.</p>
        @endforelse
    </div>

    <div class="mt-6 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Actions (run on transition)</h3>
                <p class="text-sm text-gray-500">Wire an app-registered action to a transition — no code.</p>
            </div>
            <x-filament::button size="sm" color="gray" wire:click="addAction" icon="heroicon-o-plus">
                Add action
            </x-filament::button>
        </div>

        @forelse ($actions as $i => $rule)
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                <select wire:model="actions.{{ $i }}.from" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">from…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-gray-400">&rarr;</span>
                <select wire:model="actions.{{ $i }}.to" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">to…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">run</span>
                <select wire:model="actions.{{ $i }}.action" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">action…</option>
                    @foreach ($this->actionOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="removeAction({{ $i }})" class="text-danger-600 hover:text-danger-500">&times;</button>
            </div>
        @empty
            <p class="text-sm text-gray-400">No actions wired. Your app registers available actions in the ActionCatalog.</p>
        @endforelse
    </div>

    <div class="mt-6 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Approvals (sign-off on transition)</h3>
                <p class="text-sm text-gray-500">Require ordered sign-off before a transition — set the steps, no code.</p>
            </div>
            <x-filament::button size="sm" color="gray" wire:click="addApproval" icon="heroicon-o-plus">
                Add approval
            </x-filament::button>
        </div>

        @forelse ($approvals as $i => $rule)
            <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                <select wire:model="approvals.{{ $i }}.from" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">from…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-gray-400">&rarr;</span>
                <select wire:model="approvals.{{ $i }}.to" class="rounded-md border-gray-300 text-sm dark:bg-gray-800">
                    <option value="">to…</option>
                    @foreach ($this->stateOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">needs sign-off</span>
                <input wire:model="approvals.{{ $i }}.steps" placeholder="manager, finance, ceo" class="grow rounded-md border-gray-300 text-sm dark:bg-gray-800">
                <button type="button" wire:click="removeApproval({{ $i }})" class="text-danger-600 hover:text-danger-500">&times;</button>
            </div>
        @empty
            <p class="text-sm text-gray-400">No approvals required.</p>
        @endforelse
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/drawflow@0.0.59/dist/drawflow.min.js"></script>
        <script>
            function workflowDesigner(initial) {
                return {
                    graph: initial,
                    editor: null,
                    initialKey: (initial.nodes.find(n => n.initial) || initial.nodes[0] || {}).key || '',
                    hint: '',

                    nodeHtml() {
                        return '<div class="wf-node"><input df-key class="wf-key" placeholder="state"></div>';
                    },

                    boot() {
                        if (typeof Drawflow === 'undefined') {
                            this.hint = 'editor failed to load';
                            return;
                        }

                        this.editor = new Drawflow(this.$refs.canvas);
                        this.editor.reroute = true;
                        this.editor.start();

                        this.editor.on('nodeRemoved', () => this.updateHint());
                        this.editor.on('connectionCreated', () => this.updateHint());
                        this.editor.on('connectionRemoved', () => this.updateHint());

                        this.importGraph(this.graph);

                        this.$wire.on('workflow-loaded', async () => {
                            const fresh = await this.$wire.get('graph');
                            this.importGraph(fresh);
                        });
                    },

                    importGraph(graph) {
                        if (! this.editor) {
                            return;
                        }
                        this.editor.clear();
                        this.graph = graph;
                        this.initialKey = (graph.nodes.find(n => n.initial) || graph.nodes[0] || {}).key || '';

                        const drawId = {};
                        let x = 60;

                        graph.nodes.forEach((node, index) => {
                            const id = this.editor.addNode(
                                node.key, 1, 1, x, 70 + (index % 2) * 130, 'wf-node',
                                { key: node.key }, this.nodeHtml(),
                            );
                            drawId[node.id] = id;
                            x += 190;
                        });

                        graph.edges.forEach((edge) => {
                            if (drawId[edge.from] && drawId[edge.to]) {
                                this.editor.addConnection(drawId[edge.from], drawId[edge.to], 'output_1', 'input_1');
                            }
                        });

                        this.updateHint();
                    },

                    addState() {
                        if (! this.editor) {
                            return;
                        }
                        this.editor.addNode('state', 1, 1, 80, 80, 'wf-node', { key: '' }, this.nodeHtml());
                        this.updateHint();
                    },

                    snapshot() {
                        const data = this.editor.export().drawflow.Home.data;
                        const nodes = [];
                        const edges = [];

                        Object.values(data).forEach((node) => {
                            const key = (node.data && node.data.key) ? String(node.data.key).trim() : '';
                            if (key !== '') {
                                nodes.push({ id: 'd' + node.id, key, initial: key === this.initialKey });
                            }
                        });

                        Object.values(data).forEach((node) => {
                            const conns = (node.outputs && node.outputs.output_1 && node.outputs.output_1.connections) || [];
                            conns.forEach((conn) => {
                                const from = data[node.id], to = data[conn.node];
                                const fromKey = from && from.data ? String(from.data.key || '').trim() : '';
                                const toKey = to && to.data ? String(to.data.key || '').trim() : '';
                                if (fromKey !== '' && toKey !== '') {
                                    edges.push({ from: 'd' + node.id, to: 'd' + conn.node });
                                }
                            });
                        });

                        return { nodes, edges };
                    },

                    updateHint() {
                        if (! this.editor) {
                            return;
                        }
                        const snap = this.snapshot();
                        this.hint = snap.nodes.length + ' states, ' + snap.edges.length + ' transitions';
                    },

                    async sync() {
                        if (! this.editor) {
                            return;
                        }
                        const snap = this.snapshot();
                        this.graph = { ...this.graph, nodes: snap.nodes, edges: snap.edges };
                        await this.$wire.set('graph', this.graph);
                    },
                };
            }
        </script>
    @endpush
</x-filament-panels::page>
