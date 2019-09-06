import * as express from "express";
import * as bodyParser from "body-parser";
import Pool from "./Service/Pool/Pool";
import PoolManager from "./Service/Pool/PoolManager";
import PoolTags from "./Service/Pool/PoolTags";
import RabbitMQConnector from "./Service/Queue/RabbitMQConnector";

class App {
    public app: express.Application;
    private readonly poolManager: PoolManager;

    constructor() {
        this.app = express();
        this.config();
        this.poolManager = new PoolManager();
    }

    public getPoolManager() {
        return this.poolManager;
    }

    private config(): void {
        this.app.use(bodyParser.json());
        this.app.use(bodyParser.urlencoded({ extended: false }));
    }
}


const queue: string = "notifications.websocket";
const rmqConnectionString: string = "amqp://admin:y6zYmsTtg4fNM95U@masterhome-rabbitmq:5672";
const rmqConnector = new RabbitMQConnector(queue, rmqConnectionString);
const application = new App();
const app = application.app;
const poolManager = application.getPoolManager();
poolManager.add(PoolTags.Notifications, new Pool(PoolTags.Notifications));
poolManager.add(PoolTags.Messages, new Pool(PoolTags.Messages));

export { app, poolManager, rmqConnector };
