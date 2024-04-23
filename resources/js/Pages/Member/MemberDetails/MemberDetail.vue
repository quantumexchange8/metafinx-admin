<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import {ref, onMounted} from "vue";
import Button from "@/Components/Button.vue";
import {
    ChevronRight,
    UnverifiedIcon,
    VerifiedIcon,
    PhoneIcon,
    CountryIcon,
    PassportIcon,
    IdNoIcon,
    RankIcon,
    Rank1Icon,
    Rank2Icon,
    Rank3Icon,
    ReferralIcon,
    ProofIcon,
    Wallet,
    InternalMUSDWalletIcon,
    XLCoinLogo,
    DuplicateIcon,
} from "@/Components/Icons/outline.jsx";
import {RefreshIcon} from "@heroicons/vue/outline";
import Modal from "@/Components/Modal.vue";
import MemberInvestment from "@/Pages/Member/MemberDetails/Partials/MemberInvestment.vue";
import Action from "@/Pages/Member/MemberDetails/Partials/Action.vue";
import AccountInformation from "@/Pages/Member/MemberDetails/Partials/AccountInformation.vue";
import EarningInformation from "@/Pages/Member/MemberDetails/Partials/EarningInformation.vue";
import {transactionFormat} from "@/Composables/index.js";
import toast from "@/Composables/toast.js";
import Tooltip from "@/Components/Tooltip.vue";
import { CreditEditIcon } from "@/Components/Icons/outline.jsx";
import PaymentAccountDetail from "@/Pages/Member/MemberDetails/Partials/PaymentAccountDetail.vue"

const props = defineProps({
    wallets: Object,
    member_details: Object,
    coins: Object,
    setting_coin: Object,
    upline_member: Object,
    investments: Object,
    settingRank: Array,
    walletSum: Number,
    referralCount: Number,
    self_deposit: Number,
    valid_affiliate_deposit: Number,
    paymentAccounts: Object,
})

const isLoading = ref(false);
const refresh = ref(false);
const frontIdentityModal = ref(false);
const backIdentityModal = ref(false);
const { formatAmount } = transactionFormat();
const tooltipContent = ref('Copy');
const paymentModal = ref(false);
const paymentDetails = ref(null);

function refreshTable() {
    isLoading.value = !isLoading.value;
    refresh.value = true;
}

const getMediaUrlByCollection = (user, collectionName) => {
    const media = user.media;
    const foundMedia = media.find((m) => m.collection_name === collectionName);
    return foundMedia ? foundMedia.original_url : 'https://img.freepik.com/free-icon/user_318-159711.jpg';
};

const getMediaNameByCollection = (member_details, collectionName) => {
    const media = member_details.media;
    const foundMedia = media.find((m) => m.collection_name === collectionName);
    return foundMedia ? foundMedia.file_name : 'N/A';
};

const hasMediaCollection = (member_details, collectionName) => {
    return member_details.media.some((m) => m.collection_name === collectionName);
};

const openFrontIdentityModal = () => {
    frontIdentityModal.value = true
}

const openBackIdentityModal = () => {
    backIdentityModal.value = true
}

const backButton = () => {
    frontIdentityModal.value = false
    backIdentityModal.value = false
}

function copyTestingCode () {
    let walletAddressCopy = document.querySelector('#XLCoinAddress')
    walletAddressCopy.setAttribute('type', 'text');
    walletAddressCopy.select();

    try {
        var successful = document.execCommand('copy');
        if (successful) {
            tooltipContent.value = 'Copied!';
            setTimeout(() => {
                tooltipContent.value = 'Copy'; // Reset tooltip content to 'Copy' after 2 seconds
            }, 1000);
        } else {
            tooltipContent.value = 'Try Again Later!';
        }

    } catch (err) {
        alert('Oops, unable to copy');
    }

    /* unselect the range */
    walletAddressCopy.setAttribute('type', 'hidden')
    window.getSelection().removeAllRanges()
}

const openModal = (paymentAccount) => {
    paymentModal.value = true;
    paymentDetails.value = paymentAccount;
}

const closeModal = () => {
    paymentModal.value = false
}

const handleCloseModal = () => {
    closeModal(); 
}

const copyAccountNumber = (accountNumber) => {
    const tempInput = document.createElement('input');
    tempInput.value = accountNumber;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);

    toast.add({
        message: 'Copy Successful!',
    });
}

const screenWidth = ref(window.innerWidth);

onMounted(() => {
    window.addEventListener('resize', () => {
        screenWidth.value = window.innerWidth;
    });
});

const truncatedAccountNumber = (accountNumber) => {
    let maxLength = 20; // Default max length for mobile screens

    if (accountNumber.length > maxLength) {
        return accountNumber.slice(0, maxLength) + "...";
    } else {
        return accountNumber;
    }
}

</script>

<template>
    <AuthenticatedLayout title="Member Details">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-sm font-semibold dark:text-gray-400">
                    <a class="dark:hover:text-white" href="/member/listing">Member Listing</a>
                </h2>
                <ChevronRight aria-hidden="true" class="w-6 h-6" />
                <h2 class="text-sm font-semibold dark:text-white">
                    {{ member_details.name }} - View Details
                </h2>
            </div>
        </template>

        <div class="items-center p-5 mb-8 text-base text-gray-800 rounded-xl bg-gray-50 dark:bg-gray-700">
            <div class="flex gap-3 justify-between w-full pb-5 border-b border-gray-600 flex-row">
                <div class="flex items-center gap-3">
                    <div class="flex flex-shrink-0 relative">
                        <img
                            class="object-cover w-16 h-16 rounded-full"
                            :src="getMediaUrlByCollection(member_details, 'profile_photo')"
                            alt="memberPic"
                        />
                        <UnverifiedIcon v-if="member_details.kyc_approval === 'unverified'" aria-hidden="true" class="w-4 h-4 absolute right-0 bottom-0" />
                        <VerifiedIcon v-if="member_details.kyc_approval === 'verified'" aria-hidden="true" class="w-4 h-4 absolute right-0 bottom-0" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="text-xl font-semibold dark:text-white">
                            {{ member_details.name }}
                        </h3>
                        <p class="text-sm font-normal dark:text-gray-400">
                            {{ member_details.email }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-row items-start gap-3 justify-end">
                    <Action
                        type="member"
                        :member_details="member_details"
                        :upline_member="upline_member"
                        :settingRank="settingRank"
                    />
                </div>
            </div>
            <div class="flex flex-col w-full pt-5 gap-5">
                <div class="flex flex-col md:flex-row gap-5">
                   <div class="flex items-center gap-3 w-full">
                        <CountryIcon aria-hidden="true" class="w-5 h-5" />
                        <p class="text-base dark:text-white">{{ member_details.country }}</p>
                   </div>
                    <div class="flex items-center gap-3 w-full">
                        <PhoneIcon aria-hidden="true" class="w-5 h-5" />
                        <p class="text-base dark:text-white">{{ member_details.phone }}</p>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-5">
                   <div class="flex items-center gap-3 w-full">
                        <PassportIcon aria-hidden="true" class="w-5 h-5" />
                        <p class="text-base dark:text-white uppercase">{{ member_details.verification_type }}</p>
                   </div>
                    <div class="flex items-center gap-3 w-full">
                        <IdNoIcon aria-hidden="true" class="w-5 h-5" />
                        <p class="text-base dark:text-white">{{ member_details.identity_number }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-5 font-semibold">
                        <div class="flex flex-row items-center gap-3 w-full">
                            <RankIcon aria-hidden="true" class="w-5 h-5" />
                            <div class="">
                                <span v-if="member_details.setting_rank_id === 1" class="flex flex-row items-center gap-2 text-base dark:text-white">Member</span>
                                <span v-if="member_details.setting_rank_id === 2" class="flex flex-row items-center gap-2 text-base dark:text-white">
                                    <Rank1Icon class="w-5 h-5" />LVL 1
                                </span>
                                <span v-if="member_details.setting_rank_id === 3" class="flex flex-row items-center gap-2 text-base dark:text-white">
                                    <Rank2Icon class="w-5 h-5" />LVL 2
                                </span>
                                <span v-if="member_details.setting_rank_id === 4" class="flex flex-row items-center gap-2 text-base dark:text-white">
                                    <Rank3Icon class="w-5 h-5" />LVL 3
                                </span>
                            </div>
                       </div>
                       <div class="flex items-center gap-3 w-full">
                            <ReferralIcon aria-hidden="true" class="w-5 h-5" />
                            <div class="inline-flex items-center justify-center gap-2">
                                <img
                                    class="object-cover w-5 h-5 rounded-full"
                                    :src="getMediaUrlByCollection(upline_member, 'profile_photo')"
                                    alt="uplinePic"
                                />
                                <span class="text-base dark:text-white">{{ upline_member.name }}</span>
                            </div>
                       </div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-5">
                        <div class="flex items-center gap-3 w-full">
                            <Modal :show="frontIdentityModal" :title="'Proof of ID (FRONT)'" @close="backButton">
                                <div class="relative bg-white rounded-lg shadow dark:bg-dark-eval-1">
                                    <div class="flex justify-center">
                                        <img class="rounded" :src="getMediaUrlByCollection(member_details, 'front_identity')" alt="Proof of Identity (Front)">
                                    </div>
                                </div>
                            </Modal>
                            <ProofIcon aria-hidden="true" class="w-5 h-5" />
                            <a v-if="hasMediaCollection(member_details, 'front_identity')" href="javascript:void(0);" @click.prevent="openFrontIdentityModal" class="hover:text-pink-500 dark:text-white dark:hover:text-pink-400 underline">
                                {{ getMediaNameByCollection(member_details, 'front_identity') }}
                            </a>
                            <span v-else class="dark:text-white">No File Submitted</span>
                       </div>
                       <div class="flex items-center gap-3 w-full">
                            <Modal :show="backIdentityModal" :title="'Proof of ID (BACK)'" @close="backButton">
                                <div class="relative bg-white rounded-lg shadow dark:bg-dark-eval-1">
                                    <div class="flex justify-center">
                                        <img class="rounded" :src="getMediaUrlByCollection(member_details, 'back_identity')" alt="Proof of Identity (Back)">
                                    </div>
                                </div>
                            </Modal>
                            <ProofIcon aria-hidden="true" class="w-5 h-5" />
                            <a v-if="hasMediaCollection(member_details, 'back_identity')" href="javascript:void(0);" @click.prevent="openBackIdentityModal" class="hover:text-pink-500 dark:text-white dark:hover:text-pink-400 underline">
                            {{ getMediaNameByCollection(member_details, 'back_identity') }}
                            </a>
                            <span v-else class="dark:text-white">No File Submitted</span>
                       </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-5 mb-8">
            <h3 class="text-base font-semibold border-b border-gray-700 pb-5">
                Wallet & Assets
            </h3>
            <div class="overflow-x-auto grid grid-flow-col justify-start gap-5">
                <div v-for="wallet in props.wallets" class="flex flex-col overflow-hidden rounded-[20px] w-96 border border-gray-600">
                    <div
                        class="flex justify-between"
                        :class="{
                            'bg-gradient-to-bl from-pink-400 to-pink-600': wallet.type === 'internal_wallet',
                            'bg-gradient-to-bl from-warning-300 to-warning-500 h-32': wallet.type === 'musd_wallet',
                        }"
                    >
                        <div class="py-5 px-4 flex flex-col gap-2">
                            <div class="flex flex-col">
                                <div class="text-base font-semibold dark:text-white">
                                    {{ wallet.name }}
                                </div>
                                <div class="text-xl font-semibold dark:text-white">
                                    $ {{ formatAmount(wallet.balance) }}
                                </div>
                            </div>
                            <div class="h-6">
                                <Action
                                    type="wallet"
                                    :member_details="member_details"
                                    :wallet="wallet"
                                />
                            </div>
                        </div>
                        <Wallet v-if="wallet.type === 'internal_wallet'" class="w-32 h-32"/>
                        <img v-else-if="wallet.type === 'musd_wallet'" src="/assets/icon-no-color.png" alt="" >
                        <!-- <InternalMUSDWalletIcon v-else-if="wallet.type === 'musd_wallet'" class="w-32 h-32 text-[#ffffff33]"/> -->
                    </div>
                </div>
                <div v-for="coin in props.coins" class="flex flex-col overflow-hidden rounded-[20px] w-96 border border-gray-600">
                    <div
                        class="flex justify-between"
                        style="background: linear-gradient(251deg, #00095E 2.14%, #0359E8 97.82%);"
                    >
                        <div class="py-5 px-4 flex flex-col gap-2">
                            <div class="flex flex-col">
                                <div class="text-base font-semibold dark:text-white">
                                    {{ coin.setting_coin.name }}
                                </div>
                                <div class="text-xl font-semibold dark:text-white">
                                    {{ formatAmount(coin.unit) }} MXT 
                                </div>
                            </div>
                            <div class="pt-4 h-6 inline-flex items-center gap-2">
                                <Action
                                    type="coin"
                                    :member_details="member_details"
                                    :coin="coin"
                                    :setting_coin="setting_coin"
                                />
                                <span class="mb-1 text-xs dark:text-white">{{ coin.address }}</span>
                                <input type="hidden" id="XLCoinAddress" :value="coin.address">
                                <Tooltip :content="tooltipContent" placement="top">
                                    <DuplicateIcon aria-hidden="true" :class="['w-4 h-4 dark:text-white']" @click.stop.prevent="copyTestingCode" style="cursor: pointer" />
                                </Tooltip>
                            </div>
                        </div>
                        <XLCoinLogo class="w-32 h-32" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-5 mb-8">
            <h3 class="text-base font-semibold border-b border-gray-700 pb-5">
                Payment Account
            </h3>
            <div class="space-y-5">
                <div v-if="paymentAccounts.length === 0" class="flex justify-center">
                    No Payment Accounts
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div v-for="paymentAccount in paymentAccounts" class="flex flex-col overflow-hidden rounded-[20px] w-full border border-gray-00 dark:border-gray-800">
                        <div
                            class="flex justify-between h-32 bg-gradient-to-bl from-gray-300 to-gray-500"
                        >
                            <div class="py-5 px-4 flex flex-col gap-2">
                                <div class="flex flex-col">
                                    <div class="text-base font-semibold text-gray-100 dark:text-white">
                                        {{ paymentAccount.payment_account_name }}
                                    </div>
                                    <div class="text-xl font-semibold text-gray-100 dark:text-white flex items-center">
                                        <span>{{ truncatedAccountNumber(paymentAccount.account_no) }}</span>
                                        <div @click.prevent="copyAccountNumber(paymentAccount.account_no)" class="ml-2">
                                            <DuplicateIcon class="w-5 hover:cursor-pointer" />
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <Tooltip content="Payment Account Detail" placement="right">
                                            <Button
                                                type="button"
                                                class="justify-center p-1 w-8 h-8 relative focus:outline-none dark:bg-[#ffffff32]"
                                                variant="transparent"
                                                @click="openModal(paymentAccount)"
                                                pill
                                            >
                                                <CreditEditIcon aria-hidden="true" class="w-5 h-5 absolute" />
                                                <span class="sr-only">Payment Account Detail</span>
                                            </Button>
                                        </Tooltip>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="paymentModal" title="Payment Account Detail" @close="closeModal" max-width="lg">
            <PaymentAccountDetail :paymentDetails="paymentDetails" @closeModal="handleCloseModal"/>
        </Modal>

        <div class="flex flex-col md:flex-row items-start gap-8 text-base text-gray-800 dark:text-white">
            <AccountInformation
                :member_details="member_details"
            />
            <EarningInformation
                :member_details="member_details"
            />
        </div>

        <div class="md:hidden">
                <MemberInvestment
                    :investments="investments"
                />
        </div>
        <template #asideRight>
            <div class="inset-y-0 p-6 flex flex-col space-y-6 bg-white shadow-lg dark:bg-gray-800 border-l dark:border-gray-700 w-80 lg:w-96 fixed right-0">
                <MemberInvestment
                    :investments="investments"
                />
            </div>
        </template>
    </AuthenticatedLayout>
</template>
