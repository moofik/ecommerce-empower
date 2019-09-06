export default interface IMessageSubscriber {
    update(type: string, data: any);
}
