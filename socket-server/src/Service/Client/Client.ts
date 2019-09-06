import {Socket} from "socket.io";

export default class Client {
    private socket: Socket = null;
    private token: string = null;

    /**
     * @param socket
     * @param token
     */
    constructor(socket: Socket, token: string) {
        this.socket = socket;
        this.token = token;
    }

    /**
     * @return Socket
     */
    public getSocket(): Socket {
        return this.socket;
    }

    /**
     * @return string
     */
    public getToken(): string {
        return this.token;
    }
}
