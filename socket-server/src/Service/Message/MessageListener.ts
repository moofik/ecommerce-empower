import RabbitMQConnector from "../Queue/RabbitMQConnector";
import MessageTypes from "./MessageTypes";
import IMessageSubscriber from "./IMessageSubscriber";

export default class MessageListener {
    private rmqConnector: RabbitMQConnector;

    /**
     * @param rmqConnector
     */
    constructor(rmqConnector: RabbitMQConnector) {
        this.rmqConnector = rmqConnector;
    }

    public async listen(subscriber: IMessageSubscriber) {
        await this.rmqConnector.connect();
        this.rmqConnector
            .listen()
            .subscribe((reply): any => {
                switch (reply.type) {
                    case MessageTypes.Notification:
                        subscriber.update(MessageTypes.Notification, reply);
                        break;
                    case MessageTypes.Message:
                        subscriber.update(MessageTypes.Message, reply);
                        break;
                    default:
                        break;
                }
        });
    }
}
