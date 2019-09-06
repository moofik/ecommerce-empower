import * as amqp from "amqplib";
import { Channel, Connection } from "amqplib";
import { Observable, Observer } from "rxjs";
import IMessage from "../Message/IMessage";

export default class RMQConnector {
    private channel: Channel;
    private rmqConnection: Connection;
    private connectionString: string;
    private queue: string;
    private uniqId: string;

    constructor(queue: string, connectionString: string) {
        this.queue = queue;
        this.connectionString = connectionString;
    }

    public async connect() {
        const rmqConnection = await amqp.connect(this.connectionString);
        this.channel = await rmqConnection.createChannel();
        await this.channel.assertQueue(this.queue, { durable: true });
        this.uniqId = Math
            .random()
            .toString(36)
            .substring(2, 15) + Math
            .random()
            .toString(36)
            .substring(2, 15);
    }

    public async disconnect() {
        this.channel.cancel(this.uniqId).then( () => {
            this.rmqConnection.close();
        });
    }

    public listen() {
        return new Observable<IMessage>((observer: any) => {
            this.channel.consume(this.queue, (msg) => {
                const message = JSON.parse(msg.content.toString());
                observer.next(message);
            }, { noAck: true, consumerTag: this.uniqId });
        });
    }
}
