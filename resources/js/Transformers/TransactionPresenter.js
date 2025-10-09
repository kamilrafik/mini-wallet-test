import {TransactionDetailsPresentedDto} from "@/Dtos/TransactionDetailsPresentedDto.js";
import {TransactionDetailsDto} from "@/Dtos/TransactionDetailsDto.js";
import {Formatter} from "@/Utils/Formatter.js";
import {TransactionSign} from "@/Dtos/Enums/TransactionSign.js";
import {TransactionColor} from "@/Transformers/Enums/TransactionColor.js";

export class TransactionPresenter {
    /**
     * @param currentUser
     * @param transaction {TransactionDetailsDto}
     * @returns {TransactionDetailsPresentedDto}
     */
    static map(currentUser, transaction) {
        let sign = transaction.sign
        if (sign === null) {
            if ((transaction.senderUuid !== null) && (currentUser.uuid === transaction.senderUuid)) {
                sign = TransactionSign.MINUS
            } else if ((transaction.receiverUuid !== null) && (currentUser.uuid === transaction.receiverUuid)) {
                sign = TransactionSign.PLUS
            }
        }
        if (sign === null) {
            sign = ''
        }

        let amount = null;
        if (sign === TransactionSign.PLUS) {
            amount = transaction.counterpartAmount;
        } else {
            amount = transaction.amount;
        }

        let counterpart = {
            name: null,
            email: null,
            toString: function () {
                let result = [];
                if (this.name !== null) {
                    result.push(this.name)
                }
                if (this.email !== null) {
                    result.push(this.email)
                }
                result = result.join(', ')

                if (result === '') {
                    result = '{unknown}'
                }

                return result
            }
        };
        if (sign === TransactionSign.MINUS) {
            counterpart.name = transaction.receiverName
            counterpart.email = transaction.receiverEmail
        } else if (sign === TransactionSign.PLUS) {
            counterpart.name = transaction.senderName
            counterpart.email = transaction.senderEmail
        }


        return new TransactionDetailsPresentedDto(
            transaction.uuid,
            sign + Formatter.asCurrency(amount),
            Formatter.asDateTime(transaction.createdAt),
            TransactionColor[sign],
            counterpart.toString()
        )
    }
}
