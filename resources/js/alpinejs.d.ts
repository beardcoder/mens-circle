declare module 'alpinejs' {
  export interface PluginCallback {
    (alpine: Alpine): void;
  }

  export interface Alpine {
    plugin(callback: PluginCallback): void;
    data(name: string, callback: (...args: unknown[]) => unknown): void;
    start(): void;
    initTree(el: Element): void;
    destroyTree(el: Element): void;
  }

  const Alpine: Alpine;

  export default Alpine;
}
