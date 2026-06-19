<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/drawflow@0.0.59/dist/drawflow.min.css">

    <style>
        .wf-canvas {
            height: 52vh;
            border-radius: 0.75rem;
            border: 1px solid rgb(228 228 231);
            background-color: rgb(250 250 250);
            background-image: radial-gradient(rgb(212 212 216) 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .dark .wf-canvas { border-color: rgb(63 63 70); background-color: rgb(24 24 27); background-image: radial-gradient(rgb(63 63 70) 1px, transparent 1px); }
        .wf-key { width: 120px; border: 1px solid rgb(212 212 216); border-radius: 8px; padding: 6px 10px; font-weight: 600; font-size: 13px; color: rgb(24 24 27); }
        .dark .wf-key { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #fff; }
        .drawflow .drawflow-node { border-radius: 12px; border: 1px solid rgb(212 212 216); background: #fff; box-shadow: 0 4px 12px rgba(24,24,27,.08); padding: 6px; min-width: 0; }
        .dark .drawflow .drawflow-node { background: rgb(39 39 42); border-color: rgb(63 63 70); }
        .drawflow .connection .main-path { stroke: rgb(99 102 241); stroke-width: 2.5px; }
        .wf-rule { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .wf-rule > * { min-width: 0; }
    </style>

    {{-- Toolbar: pick & load a workflow --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="w-64">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model="workflowKey">
                    <option value="">✨ Start a new workflow…</option>
                    @foreach ($this->workflowOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
        <x-filament::button color="gray" wire:click="load" icon="heroicon-m-arrow-down-tray">
            Load for editing
        </x-filament::button>
    </div>

    {{-- The visual process map --}}
    <x-filament::section icon="heroicon-o-share">
        <x-slot name="heading">Process map</x-slot>
        <x-slot name="description">Drag to move, drag dot-to-dot to connect, right-click to delete. Rename a state in its box.</x-slot>

        <div wire:ignore x-data="workflowDesigner(@js($graph))" x-init="boot()" class="space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button color="gray" type="button" x-on:click="addState()" icon="heroicon-m-plus">
                    Add state
                </x-filament::button>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Initial</span>
                    <div class="w-32">
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" x-model="initialKey" placeholder="draft" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <x-filament::button type="button" x-on:click="sync().then(() => $wire.save())" icon="heroicon-m-check-circle">
                    Save as new version
                </x-filament::button>

                <x-filament::badge color="info" x-text="hint"></x-filament::badge>
            </div>

            <div x-ref="canvas" class="wf-canvas"></div>
        </div>
    </x-filament::section>

    {{-- No-code: conditions --}}
    <x-filament::section icon="heroicon-o-funnel">
        <x-slot name="heading">Conditions</x-slot>
        <x-slot name="description">A transition is allowed only when its rules pass — no code.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button size="sm" color="gray" wire:click="addRule" icon="heroicon-m-plus">Add rule</x-filament::button>
        </x-slot>

        <div class="space-y-2">
            @forelse ($conditions as $i => $rule)
                <div class="wf-rule rounded-xl border border-gray-200 p-2.5 dark:border-white/10">
                    <x-filament::input.wrapper><x-filament::input.select wire:model="conditions.{{ $i }}.from"><option value="">from…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4 text-gray-400" />
                    <x-filament::input.wrapper><x-filament::input.select wire:model="conditions.{{ $i }}.to"><option value="">to…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <span class="text-sm text-gray-500">allowed if</span>
                    <x-filament::input.wrapper><x-filament::input type="text" wire:model="conditions.{{ $i }}.field" placeholder="amount" /></x-filament::input.wrapper>
                    <x-filament::input.wrapper><x-filament::input.select wire:model="conditions.{{ $i }}.operator">@foreach ($this->operatorOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <x-filament::input.wrapper><x-filament::input type="text" wire:model="conditions.{{ $i }}.value" placeholder="1000" /></x-filament::input.wrapper>
                    <x-filament::icon-button icon="heroicon-m-trash" color="danger" wire:click="removeRule({{ $i }})" label="Remove" />
                </div>
            @empty
                <p class="text-sm text-gray-400">No rules — every declared transition is allowed.</p>
            @endforelse
        </div>
    </x-filament::section>

    {{-- No-code: actions --}}
    <x-filament::section icon="heroicon-o-bolt">
        <x-slot name="heading">Actions</x-slot>
        <x-slot name="description">Run an app-registered action when a transition happens — no code.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button size="sm" color="gray" wire:click="addAction" icon="heroicon-m-plus">Add action</x-filament::button>
        </x-slot>

        <div class="space-y-2">
            @forelse ($actions as $i => $rule)
                <div class="wf-rule rounded-xl border border-gray-200 p-2.5 dark:border-white/10">
                    <x-filament::input.wrapper><x-filament::input.select wire:model="actions.{{ $i }}.from"><option value="">from…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4 text-gray-400" />
                    <x-filament::input.wrapper><x-filament::input.select wire:model="actions.{{ $i }}.to"><option value="">to…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <span class="text-sm text-gray-500">run</span>
                    <x-filament::input.wrapper><x-filament::input.select wire:model="actions.{{ $i }}.action"><option value="">action…</option>@foreach ($this->actionOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <x-filament::icon-button icon="heroicon-m-trash" color="danger" wire:click="removeAction({{ $i }})" label="Remove" />
                </div>
            @empty
                <p class="text-sm text-gray-400">No actions wired. Your app registers available actions in the ActionCatalog.</p>
            @endforelse
        </div>
    </x-filament::section>

    {{-- No-code: approvals --}}
    <x-filament::section icon="heroicon-o-check-badge">
        <x-slot name="heading">Approvals</x-slot>
        <x-slot name="description">Require ordered sign-off before a transition — no code.</x-slot>
        <x-slot name="headerEnd">
            <x-filament::button size="sm" color="gray" wire:click="addApproval" icon="heroicon-m-plus">Add approval</x-filament::button>
        </x-slot>

        <div class="space-y-2">
            @forelse ($approvals as $i => $rule)
                <div class="wf-rule rounded-xl border border-gray-200 p-2.5 dark:border-white/10">
                    <x-filament::input.wrapper><x-filament::input.select wire:model="approvals.{{ $i }}.from"><option value="">from…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="h-4 w-4 text-gray-400" />
                    <x-filament::input.wrapper><x-filament::input.select wire:model="approvals.{{ $i }}.to"><option value="">to…</option>@foreach ($this->stateOptions() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</x-filament::input.select></x-filament::input.wrapper>
                    <span class="text-sm text-gray-500">needs sign-off</span>
                    <div class="grow"><x-filament::input.wrapper><x-filament::input type="text" wire:model="approvals.{{ $i }}.steps" placeholder="manager, finance, ceo" /></x-filament::input.wrapper></div>
                    <x-filament::icon-button icon="heroicon-m-trash" color="danger" wire:click="removeApproval({{ $i }})" label="Remove" />
                </div>
            @empty
                <p class="text-sm text-gray-400">No approvals required.</p>
            @endforelse
        </div>
    </x-filament::section>

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
