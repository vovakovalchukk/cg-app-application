<?php
namespace CG\Courier\Geopost\Command;

use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\CredentialsInterface as AccountCredentials;
use CG\Account\Shared\Collection as Accounts;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\CourierAdapter\Provider\Credentials as CourierAdapterCredentials;
use CG\Courier\Geopost\CourierAbstract as GeopostAbstract;
use CG\Courier\Geopost\Command\UpdateConsignmentAndParcelRange\Changes;
use CG\Courier\Geopost\Interlink\Courier as CourierInterlink;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\CourierAdapter\StorageInterface as CourierAdapterStorage;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class UpdateConsignmentAndParcelRange implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE = 'GeopostUpdateConsignmentAndParcelRange';
    protected const PROMPTS_BY_ACCOUNT_TYPE = [
        'dpd-ca' => [
            'promptForParcelNumbers',
        ],
        'interlink-ca' => [
            'promptForParcelNumbers',
            'promptForConsignmentNumbers',
        ],
    ];
    protected const PROMPT_ACCOUNT_ID = 'Please enter CG\'s internal account ID for the shipping channel: ';
    protected const PROMPT_PARCEL_NUMBER = 'Please enter the start and end numbers for the parcel number range, separated by a space (leave blank if not required). Current range: %s: ';
    protected const PROMPT_CONSIGNMENT_NUMBER = 'Please enter the start and end numbers for the consignment number range, separated by a space (leave blank if not required). Current range: %s: ';
    protected const PROMPT_CONFIRM_CHANGES = 'Do you wish to change %s number range from %s to %s (y/n)? ';
    protected const PROMPT_REJECTED_CHANGES = 'Changes rejected for %s range';
    protected const PROMPT_TYPE_CONSIGNMENT = 'consignment';
    protected const PROMPT_TYPE_PARCEL = 'parcel';
    protected const KEY_TEMPLATE_PARCEL_NUMBER = GeopostAbstract::CACHE_PARCEL_NUMBER_KEY_TEMPLATE;
    protected const STORAGE_SUFFIX_CONSIGNMENT_NUMBER = CourierInterlink::STORAGE_SUFFIX_CONSIGNMENT_NO;
    protected const LOG_MSG_ACCOUNT_NOT_FOUND = 'No Geopost account with id %s found';
    protected const LOG_MSG_BAD_INPUT = 'Invalid input %s';

    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;
    /** @var QuestionHelper */
    protected $questionHelper;
    /** @var AccountService */
    protected $accountService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var CourierAdapterStorage */
    protected $courierAdapterStorage;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        AccountService $accountService,
        Cryptor $cryptor,
        CourierAdapterStorage $courierAdapterStorage
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
        $this->courierAdapterStorage = $courierAdapterStorage;
    }

    public function __invoke(): void
    {
        if (!($account = $this->fetchAccount())) {
            return;
        }
        $changes = new Changes();
        /** @var CourierAdapterCredentials $credentials */
        $credentials = $this->cryptor->decrypt($account->getCredentials());
        $this->updateChangesFromCredentials($changes, $credentials);
        foreach ($this->getPromptsByAccountType($account) as $prompt) {
            $this->$prompt($changes);
        }
        $this->confirmAndApplyChanges($account, $credentials, $changes);
    }

    protected function fetchAccount(): ?Account
    {
        $accountId = $this->promptForAccountId();
        try {
            /** @var Accounts $accounts */
            $accounts = $this->accountService->fetchByFilter(
                (new AccountFilter('all', 1))
                    ->setId([$accountId])
                    ->setChannel(['dpd-ca', 'interlink-ca'])
            );
            /** @var ?Account $account */
            $account = $accounts->getFirst();
            $this->validateAccount($account);
            return $account;
        } catch (NotFound $e) {
            $this->logDebug(static::LOG_MSG_ACCOUNT_NOT_FOUND, [$accountId], static::LOG_CODE);
            $this->output->writeln(sprintf(static::LOG_MSG_ACCOUNT_NOT_FOUND, $accountId));
            return null;
        } catch (\InvalidArgumentException $e) {
            $this->logDebugException($e, __METHOD__, [], static::LOG_CODE);
            $this->output->writeln($e->getMessage());
            return null;
        }
    }

    protected function validateAccount(?Account $account): void
    {
        if ($account === null) {
            throw new NotFound();
        }
    }

    protected function promptForAccountId(): int
    {
        $question = new Question(static::PROMPT_ACCOUNT_ID);
        $question->setValidator(function ($response) {
            if ((int)$response <= 0) {
                throw new \InvalidArgumentException(sprintf(static::LOG_MSG_BAD_INPUT, (string)$response));
            }
            return (int)$response;
        });
        return $this->questionHelper->ask($this->input, $this->output, $question);
    }

    protected function updateChangesFromCredentials(Changes $changes, AccountCredentials $credentials): void
    {
        $credentialsData = $credentials->toArray();
        if (isset($credentialsData['parcelStart'])) {
            $changes->setOriginalParcelNumberStart($credentialsData['parcelStart']);
        }
        if (isset($credentialsData['parcelEnd'])) {
            $changes->setOriginalParcelNumberEnd($credentialsData['parcelEnd']);
        }
        if (isset($credentialsData['consignmentStart'])) {
            $changes->setOriginalConsignmentNumberStart($credentialsData['consignmentStart']);
        }
        if (isset($credentialsData['consignmentEnd'])) {
            $changes->setOriginalConsignmentNumberEnd($credentialsData['consignmentEnd']);
        }
    }

    protected function getPromptsByAccountType(Account $account): \Generator
    {
        if (!isset(static::PROMPTS_BY_ACCOUNT_TYPE[$account->getChannel()])) {
            throw new \InvalidArgumentException();
        }
        yield from static::PROMPTS_BY_ACCOUNT_TYPE[$account->getChannel()];
    }

    protected function promptForParcelNumbers(Changes $changes): void
    {
        $question = new Question(sprintf(static::PROMPT_PARCEL_NUMBER, $changes->getCurrentParcelRange()));
        $response = $this->questionHelper->ask($this->input, $this->output, $question);
        if (empty($response)) {
            return;
        }
        [$parcelStart, $parcelEnd] = explode(' ', $response, 2);
        $changes
            ->setNewParcelNumberStart($parcelStart)
            ->setNewParcelNumberEnd($parcelEnd);
    }

    protected function promptForConsignmentNumbers(Changes $changes): void
    {
        $question = new Question(sprintf(static::PROMPT_CONSIGNMENT_NUMBER, $changes->getCurrentConsignmentRange()));
        $response = $this->questionHelper->ask($this->input, $this->output, $question);
        if (empty($response)) {
            return;
        }
        [$consignmentStart, $consignmentEnd] = explode(' ', $response, 2);
        $changes
            ->setNewConsignmentNumberStart($consignmentStart)
            ->setNewConsignmentNumberEnd($consignmentEnd);
    }

    protected function confirmAndApplyChanges(Account $account, CourierAdapterCredentials $credentials, Changes $changes): void
    {
        if ($changes->consignmentRangeChanged()) {
            $question = new ConfirmationQuestion(
                sprintf(
                    static::PROMPT_CONFIRM_CHANGES,
                    static::PROMPT_TYPE_CONSIGNMENT,
                    $changes->getCurrentConsignmentRange(),
                    $changes->getNewConsignmentRange()
                ),
                false
            );
            $updateConsignmentNumbers = $this->questionHelper->ask($this->input, $this->output, $question);
            if ($updateConsignmentNumbers) {
                $credentials
                    ->set('consignmentStart', $changes->getNewConsignmentNumberStart())
                    ->set('consignmentEnd', $changes->getNewConsignmentNumberEnd());
                $this->courierAdapterStorage->set(
                    $account->getId() . static::STORAGE_SUFFIX_CONSIGNMENT_NUMBER,
                    $changes->getNewParcelNumberStart()
                );
            } else {
                $this->output->writeln(sprintf(static::PROMPT_REJECTED_CHANGES, static::PROMPT_TYPE_PARCEL));
            }
        }
        if ($changes->parcelRangeChanged()) {
            $question = new ConfirmationQuestion(
                sprintf(
                    static::PROMPT_CONFIRM_CHANGES,
                    static::PROMPT_TYPE_PARCEL,
                    $changes->getCurrentParcelRange(),
                    $changes->getNewParcelRange()
                ),
                false
            );
            $updateParcelNumbers = $this->questionHelper->ask($this->input, $this->output, $question);
            if ($updateParcelNumbers) {
                $credentials
                    ->set('parcelStart', $changes->getNewParcelNumberStart())
                    ->set('parcelEnd', $changes->getNewParcelNumberEnd());
                $this->courierAdapterStorage->set(
                    sprintf(static::KEY_TEMPLATE_PARCEL_NUMBER, $account->getId()),
                    $changes->getNewParcelNumberStart()
                );
            } else {
                $this->output->writeln(sprintf(static::PROMPT_REJECTED_CHANGES, static::PROMPT_TYPE_PARCEL));
            }
        }
        $account->setCredentials($this->cryptor->encrypt($credentials));
        $this->accountService->save($account);
    }
}