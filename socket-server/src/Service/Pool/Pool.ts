import Client from "../Client/Client";

export default class Pool {
    /** List of clients in the pool */
    private clients: Map<string, Client>;
    private readonly tag: string;

    /**
     * @param tag
     */
    constructor(tag: string) {
        this.clients = new Map<string, Client>();
        this.tag = tag;
    }

    /**
     * @param accessToken
     */
    public findClient(accessToken: string): Client {
        return this.clients.get(accessToken);
    }

    /**
     * Add client to list of managed connections
     * @param client
     * @param token
     */
    public register(client: Client, token: string) {
        const socketId = client.getSocket().id;
        console.log(`New user registered in ${this.tag} pool with ${token}`);
        this.clients.set(token, client);
        this.removeClientOnDisconnect(client);
    }

    /**
     * @param client
     */
    private removeClientOnDisconnect(client: Client): void {
        const socket = client.getSocket();

        socket.on("disconnect", () => {
            this.clients.delete(client.getToken());
        });
    }
}
