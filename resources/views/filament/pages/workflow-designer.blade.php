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
            style="height: 60vh; border: 1px solid rgb(229 231 235); border-radius: 0.75rem; background:
                radial-gradient(rgb(229 231 235) 1px, transparent 1px); background-size: 18px 18px;"
        ></div>
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
