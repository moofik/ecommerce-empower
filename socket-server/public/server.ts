import {app, poolManager, rmqConnector} from "../src/Application";
import { createServer } from "http";
import Server from "../src/SocketServer";

const server = createServer(app);
server.listen(3335,  () => {
    console.log("Web chat server is listening on port 3335");
});
// server.listen(3336, () => {
//     console.log("Web chat server is listening on port 3336 also");
// });

const SocketServer = new Server(server, poolManager, rmqConnector);
SocketServer.run();
