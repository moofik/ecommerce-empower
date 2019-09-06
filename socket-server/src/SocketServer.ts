import * as io from "socket.io";
import { Server } from "http";
import PoolManager from "./Service/Pool/PoolManager";
import PoolTags from "./Service/Pool/PoolTags";
import Client from "./Service/Client/Client";
import MessageListener from "./Service/Message/MessageListener";
import MessageTypes from "./Service/Message/MessageTypes";
import IMessage from "./Service/Message/IMessage";
import IMessageSubscriber from "./Service/Message/IMessageSubscriber";
import RabbitMQConnector from "./Service/Queue/RabbitMQConnector";

export default class SocketServer implements IMessageSubscriber {
    private io: SocketIO.Server;
    private poolManager: PoolManager;
    private messageListener: MessageListener;

    /**
     * @param server
     * @param poolManager
     * @param rmqConnector
     */
    constructor(
        server: Server,
        poolManager: PoolManager,
        rmqConnector: RabbitMQConnector,
    ) {
        this.io = io.listen(server);
        this.poolManager = poolManager;
        this.messageListener = new MessageListener(rmqConnector);
        this.messageListener.listen(this);
    }

    public run() {
        this.io.on("connection", (socket: any) => {
            console.log(`Client connected, socket id: ${socket.id}`);
            socket.on("subscribe", (msg) => {
                const client = new Client(socket, msg.token);
                console.log(`Subscribe with ${msg.token}`);
                console.log(msg);
                this.poolManager
                    .get(PoolTags.Notifications)
                    .register(client, msg.token);

                this.poolManager
                    .get(PoolTags.Messages)
                    .register(client, msg.token);
            });

            socket.on("disconnect", () => {
                console.log("Client disconnected, socket id: " + socket.id);
            });
        });
    }

    /**
     * @param type
     * @param message
     */
    public update(type: string, message: IMessage) {
        const client = this.poolManager
            .get(PoolTags.Notifications)
            .findClient(message.addressee_token);

        switch (type) {
            case MessageTypes.Notification:
                if (client) {
                    const response = {
                        is_read: message.data.is_read,
                        notification_text: message.data.notification_text,
                        type: message.data.type,
                    };

                    console.log(`Send message to socket ${client.getSocket().id}`);
                    client
                        .getSocket()
                        .emit("notification", response);
                } else {
                    console.log(`Client was not found`);
                }
                break;

            case MessageTypes.Message:
                break;

            default:
                break;
        }
    }

    private verifyToken(socket) {
        const header = socket.handshake.headers.authorization;
        const tokenParts = header.split(" ");
        return process.env.AUTH_TOKEN === tokenParts[1];
    }
}
