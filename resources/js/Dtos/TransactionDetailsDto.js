/**
 * The structure could be more deep, like 'sender' => [] etc, but it is not important for the test task.
 */
export class TransactionDetailsDto {
    constructor({
        uuid,
        amount,
        createdAt,
        sign,
        senderUuid,
        senderName,
        senderEmail,
        receiverUuid,
        receiverName,
        receiverEmail
                }) {
        this.uuid = uuid
        this.amount = amount
        this.createdAt = createdAt
        this.sign = sign
        this.senderUuid = senderUuid
        this.senderName = senderName
        this.senderEmail = senderEmail
        this.receiverUuid = receiverUuid
        this.receiverName = receiverName
        this.receiverEmail = receiverEmail
    }
}
