/* global $: true */

import React, { Component } from 'react';
import AccountCard from './account-card';
import AccountList from '../components/account-list';

class TransferAccount extends Component {

    constructor(props) {
        super(props);
        this.state = {
            open: false,
        };
    }

    componentWillUnmount() {
        $('body').off('click', this.handlePageClick);
    }

    clickEdit = () => {
        this.setState({
            open: true,
            selectedAccount: this.state.selectedAccount,
        });
        this.props.setActiveTransfer();
        $(document).on('click', this.handlePageClick);
    }

    selectAccount = (account) => {
        this.setState({
            open: false,
        });

        this.props.selectAccount(account);
        this.props.clearActiveTransfer();
        $(document).off('click', this.handlePageClick);
    }

    handlePageClick = () => {
        this.setState({
            open: false,
            selectedAccount: this.state.selectedAccount,
        });
        this.props.clearActiveTransfer();
        $(document).off('click', this.handlePageClick);
    }

    render() {
        if (this.state.open) {
            return (
                <AccountList selectedAccount={this.props.account} selectAccount={this.selectAccount} />
            );
        } else if (!this.state.open && this.props.account) {
            return (
                <ul className="card-list">
                    <li className="card-item">
                        <AccountCard
                            account={this.props.account}
                            selectAccount={this.clickEdit}
                        />
                    </li>
                </ul>
            );
        }

        return (
            <ul className="card-list">
                <li className="card-item">
                    <a className="card add" onClick={this.clickEdit}>Select account</a>
                </li>
            </ul>
        );
    }
}

TransferAccount.propTypes = {
    account: React.PropTypes.object,
    selectAccount: React.PropTypes.func,
    clearActiveTransfer: React.PropTypes.func,
    setActiveTransfer: React.PropTypes.func,
};

export default TransferAccount;
