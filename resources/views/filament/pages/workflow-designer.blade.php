<x-filament-panels::page>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/drawflow@0.0.59/dist/drawflow.min.css">

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
            <x-filament::button type="button" x-on:click="sync().then(() => $wire.save())" icon="heroicon-o-check">
                Save as new version
            </x-filament::button>
            <span class="text-sm text-gray-500" x-text="`${graph.nodes.length} states, ${graph.edges.length} transitions`"></span>
        </div>

        <div
            x-ref="canvas"
            class="fi-wo-canvas"
            style="height: 50vh; border: 1px solid rgb(229 231 235); border-radius: 0.75rem; background:
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/drawflow@0.0.59/dist/drawflow.min.js"></script>
        <script>
            function workflowDesigner(initial) {
                return {
                    graph: initial,
                    editor: null,
                    nodeIds: {},

                    boot() {
                        if (typeof Drawflow === 'undefined') {
                            return;
                        }

                        this.editor = new Drawflow(this.$refs.canvas);
                        this.editor.reroute = true;
                        this.editor.start();

                        let x = 60;
                        this.graph.nodes.forEach((node, index) => {
                            const html = `<div style="padding:6px 10px;font-weight:600">${node.key}${node.initial ? ' &#9733;' : ''}</div>`;
                            const id = this.editor.addNode(node.key, 1, 1, x, 80 + (index % 2) * 120, 'wf-node', {}, html);
                            this.nodeIds[node.id] = id;
                            x += 200;
                        });

                        this.graph.edges.forEach((edge) => {
                            const from = this.nodeIds[edge.from];
                            const to = this.nodeIds[edge.to];
                            if (from && to) {
                                this.editor.addConnection(from, to, 'output_1', 'input_1');
                            }
                        });
                    },

                    addState() {
                        const key = prompt('State key (e.g. paid)');
                        if (! key) {
                            return;
                        }
                        const id = 'n' + (this.graph.nodes.length + 1) + '_' + Date.now();
                        this.graph.nodes.push({ id, key });
                        if (this.editor) {
                            const drawId = this.editor.addNode(key, 1, 1, 80, 80, 'wf-node', {}, `<div style="padding:6px 10px;font-weight:600">${key}</div>`);
                            this.nodeIds[id] = drawId;
                        }
                    },

                    async sync() {
                        if (! this.editor) {
                            return;
                        }
                        const data = this.editor.export().drawflow.Home.data;
                        const drawToOur = {};
                        Object.values(this.nodeIds).forEach((drawId, i) => {
                            drawToOur[drawId] = Object.keys(this.nodeIds)[i];
                        });

                        const edges = [];
                        Object.values(data).forEach((node) => {
                            const outputs = node.outputs?.output_1?.connections ?? [];
                            outputs.forEach((conn) => {
                                const from = drawToOur[node.id];
                                const to = drawToOur[conn.node];
                                if (from && to) {
                                    edges.push({ from, to });
                                }
                            });
                        });

                        this.graph.edges = edges;
                        await this.$wire.set('graph', this.graph);
                    },
                };
            }
        </script>
    @endpush
</x-filament-panels::page>
