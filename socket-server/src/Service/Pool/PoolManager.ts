import Pool from "./Pool";

export default class PoolManager {
    private pools: Map<string, Pool>;

    constructor() {
        this.pools = new Map<string, Pool>();
    }

    /**
     * @param tag
     * @param pool
     */
    public add(tag: string, pool: Pool) {
        if (this.pools.has(tag)) {
            throw new Error(`Pool with tag ${tag} already exists`);
        }

        this.pools.set(tag, pool);
    }

    /**
     * @param tag
     */
    public get(tag: string) {
        return this.pools.get(tag);
    }
}
