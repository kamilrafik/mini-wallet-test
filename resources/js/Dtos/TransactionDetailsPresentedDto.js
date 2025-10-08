export class TransactionDetailsPresentedDto {
    constructor({
        uuid,
        amount,
        createdAt,
        color,
        counterpart
                }) {
        this.uuid = uuid
        this.amount = amount
        this.createdAt = createdAt
        this.color = color
        this.counterpart = counterpart
    }
}
