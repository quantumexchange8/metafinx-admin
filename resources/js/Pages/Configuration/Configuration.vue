<script setup>
import AuthenticatedLayout from "@/Layouts/Authenticated.vue";
import ConfigurationContent from "@/Pages/Configuration/Partials/ConfigurationContent.vue";
import {ref} from "vue";
import Announcement from "@/Pages/Configuration/Announcement/Announcement.vue";
import TnCSetting from "@/Pages/Configuration/TnCSetting/TnCSetting.vue";
import DividendBonus from "@/Pages/Configuration/DividendBonus/DividendBonus.vue";
import WithdrawalFee from "@/Pages/Configuration/WithdrawalFee/WithdrawalFee.vue";
import AffiliateForm from "@/Pages/Configuration/AffiliateSetting/AffliateForm.vue";
import CoinSetting from "@/Pages/Configuration/CoinSetting/CoinSetting.vue";
import MasterSetting from "@/Pages/Configuration/MasterSetting/MasterSetting.vue";
import StakingReward from "@/Pages/Configuration/StakingReward/StakingReward.vue";

const props = defineProps({
    users: Array,
    settingRanks: Object,
    settingCoin: Object,
    totalCoinSupply: Number,
    conversionRate: Object,
    coinMarketTime: Object,
    masterSetting: Object,
    stakingReward: Object,
})

const content = ref('Announcement');

const updateContent = (newContent) => {
    content.value = newContent;
}
</script>

<template>
    <AuthenticatedLayout title="Configuration">
        <div class="flex flex-col md:flex-row">
            <!-- section originally has fixed and lg:static -->
            <section
                tabindex="-1"
                class="hidden md:block static inset-y-0 z-10 flex-shrink-0 w-60 bg-white border-r dark:border-gray-700 dark:bg-gray-800 focus:outline-none"
                aria-labelledby="secondSidebarHeader"
            >
                <div class="flex flex-col h-screen">
                    <!-- Panel header -->
                    <div class="pb-4 sm:py-6 px-0">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <h2 class="text-xl md:text-2xl font-semibold leading-tight">
                                Configuration
                            </h2>
                        </div>
                    </div>

                    <!-- Panel content -->
                    <div class="flex-1 overflow-y-hidden hover:overflow-y-auto">
                        <ConfigurationContent
                            :content="content"
                            @update:content="updateContent"
                        />
                    </div>
                </div>
            </section>
            <section
                tabindex="-1"
                class="md:hidden static inset-x-0 z-10 flex-shrink-0 h-auto bg-white dark:bg-gray-800 focus:outline-none"
                aria-labelledby="secondTopbarHeader"
            >
                <div class="flex flex-col w-full">
                    <!-- Panel header -->
                    <div class="pb-4 px-0">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <h2 class="text-xl md:text-2xl font-semibold leading-tight">
                                Configuration
                            </h2>
                        </div>
                    </div>

                    <!-- Panel content -->
                    <div class="flex-1 overflow-x-hidden hover:overflow-x-auto">
                        <ConfigurationContent
                            :content="content"
                            @update:content="updateContent"
                        />
                    </div>
                </div>
            </section>
            <Announcement
                v-if="content==='Announcement'"
            />
            <TnCSetting
                v-if="content==='TnCSetting'"
            />
            <DividendBonus
                v-if="content==='DividendBonus'"
            />
            <AffiliateForm
                v-if="content==='AffiliateSetting'"
                :settingRanks="settingRanks"
            />
            <MasterSetting
                v-if="content==='MasterSetting'"
                :masterSetting="masterSetting"
            />
            <CoinSetting
                v-if="content==='CoinSetting'"
                :settingCoin="settingCoin"
                :totalCoinSupply="totalCoinSupply"
                :conversionRate="conversionRate"
                :coinMarketTime="coinMarketTime"
            />
            <StakingReward
                v-if="content==='StakingReward'"
                :stakingReward="stakingReward"
            />
        </div>

    </AuthenticatedLayout>
</template>
